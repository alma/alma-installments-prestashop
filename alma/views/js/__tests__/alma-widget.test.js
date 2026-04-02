const { initAlmaWidget, initAlmaProductWidget, findWidgetContainer, initAlmaWidgetFromContainer, getCartAmountInCents } = require('../alma-widget');

const mockCartWidgetConfig = {
    purchaseAmount: 22976,
    containerId: '#alma-widget-cart',
    merchantId: 'merchant_123',
    hideIfNotEligible: 0,
    mode: 'test',
    plans: [{ installmentsCount: 3, deferredDays: 0, minAmount: 5000, maxAmount: 300000 }],
    locale: 'en',
};

const mockProductWidgetConfig = {
    purchaseAmount: 9900,
    containerId: '#alma-widget-product',
    merchantId: 'merchant_123',
    hideIfNotEligible: 0,
    mode: 'test',
    plans: [{ installmentsCount: 3, deferredDays: 0, minAmount: 5000, maxAmount: 300000 }],
    locale: 'en',
};

const mockAlma = {
    ApiMode: { TEST: 'test', LIVE: 'live' },
    Widgets: {
        PaymentPlans: 'PaymentPlans',
        initialize: jest.fn().mockReturnValue({
            add: jest.fn(),
        }),
    },
};

const mockJQuery = ({ cartExists = false, footerExists = false, config = mockCartWidgetConfig } = {}) => {
    return jest.fn().mockImplementation((selector) => {
        if (selector === '#alma-widget-cart') {
            return {
                length: cartExists ? 1 : 0,
                data: jest.fn().mockReturnValue({ ...config }),
            };
        }
        if (selector === '#alma-widget-ShoppingCartFooter') {
            return {
                length: footerExists ? 1 : 0,
                data: jest.fn().mockReturnValue({ ...config }),
            };
        }
        return { length: 0, data: jest.fn() };
    });
};

const mockJQueryWithProduct = ({ productAdditionalInfoExists = false, productExists = false, config = mockProductWidgetConfig } = {}) => {
    return jest.fn().mockImplementation((selector) => {
        if (selector === '#alma-widget-ProductPriceBlock') {
            return {
                length: productAdditionalInfoExists ? 1 : 0,
                data: jest.fn().mockReturnValue({ ...config }),
            };
        }
        if (selector === '#alma-widget-product') {
            return {
                length: productExists ? 1 : 0,
                data: jest.fn().mockReturnValue({ ...config }),
            };
        }
        return { length: 0, data: jest.fn() };
    });
};

describe('initAlmaWidget', () => {
    beforeEach(() => {
        jest.clearAllMocks();
    });

    test('return null if container doesn\'t exist', () => {
        const $ = mockJQuery({ cartExists: false, footerExists: false });
        const result = initAlmaWidget($, mockAlma);
        expect(result).toBeNull();
    });

    test('initialize Alma TEST mode with #alma-widget-cart', () => {
        const $ = mockJQuery({ cartExists: true });
        initAlmaWidget($, mockAlma);
        expect(mockAlma.Widgets.initialize).toHaveBeenCalledWith('merchant_123', 'test');
    });

    test('initialize Alma LIVE mode with #alma-widget-cart', () => {
        const $ = mockJQuery({ cartExists: true, config: { ...mockCartWidgetConfig, mode: 'live' } });
        initAlmaWidget($, mockAlma);
        expect(mockAlma.Widgets.initialize).toHaveBeenCalledWith('merchant_123', 'live');
    });

    test('initialize Alma with #alma-widget-ShoppingCartFooter if no cart', () => {
        const $ = mockJQuery({ footerExists: true });
        initAlmaWidget($, mockAlma);
        expect(mockAlma.Widgets.initialize).toHaveBeenCalledWith('merchant_123', 'test');
    });

    test('use #alma-widget-cart priority if both exist', () => {
        const footerConfig = { ...mockCartWidgetConfig, merchantId: 'merchant_footer' };
        const cartConfig = { ...mockCartWidgetConfig, merchantId: 'merchant_cart' };
        const $ = jest.fn().mockImplementation((selector) => {
            if (selector === '#alma-widget-cart') {
                return { length: 1, data: jest.fn().mockReturnValue(cartConfig) };
            }
            if (selector === '#alma-widget-ShoppingCartFooter') {
                return { length: 1, data: jest.fn().mockReturnValue(footerConfig) };
            }
            return { length: 0 };
        });
        initAlmaWidget($, mockAlma);
        expect(mockAlma.Widgets.initialize).toHaveBeenCalledWith('merchant_cart', 'test');
    });

    test('call widgets.add with good parameters', () => {
        const $ = mockJQuery({ cartExists: true });
        const widgets = initAlmaWidget($, mockAlma);
        expect(widgets.add).toHaveBeenCalledWith('PaymentPlans', expect.objectContaining({
            purchaseAmount: 22976,
            locale: 'en',
        }));
    });

    test('parse plans if it is a string', () => {
        const config = { ...mockCartWidgetConfig, plans: '[{"installmentsCount":3}]' };
        const $ = mockJQuery({ cartExists: true, config });
        const widgets = initAlmaWidget($, mockAlma);
        expect(widgets.add).toHaveBeenCalledWith('PaymentPlans', expect.objectContaining({
            plans: [{ installmentsCount: 3 }],
        }));
    });
});

describe('getCartAmountInCents', () => {
    const originalPrestashop = global.prestashop;

    afterEach(() => {
        global.prestashop = originalPrestashop;
    });

    test('returns null when prestashop is undefined', () => {
        delete global.prestashop;
        expect(getCartAmountInCents()).toBeNull();
    });

    test('returns null when prestashop.cart is missing', () => {
        global.prestashop = {};
        expect(getCartAmountInCents()).toBeNull();
    });

    test('returns null when totals are missing', () => {
        global.prestashop = { cart: {} };
        expect(getCartAmountInCents()).toBeNull();
    });

    test('returns amount in cents from total_including_tax', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: '229.76' } } } };
        expect(getCartAmountInCents()).toBe(22976);
    });

    test('falls back to total when total_including_tax is absent', () => {
        global.prestashop = { cart: { totals: { total: { amount: '150.00' } } } };
        expect(getCartAmountInCents()).toBe(15000);
    });

    test('rounds up correctly for 3-decimal amounts', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: '10.999' } } } };
        expect(getCartAmountInCents()).toBe(1100);
    });

    test('rounds down correctly for 3-decimal amounts', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: '10.994' } } } };
        expect(getCartAmountInCents()).toBe(1099);
    });

    test('rounds correctly for amounts with more than 3 decimals', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: '229.7654' } } } };
        expect(getCartAmountInCents()).toBe(22977);
    });

    test('handles numeric (non-string) amount', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: 229.76 } } } };
        expect(getCartAmountInCents()).toBe(22976);
    });

    test('handles numeric integer amount', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: 229 } } } };
        expect(getCartAmountInCents()).toBe(22900);
    });
});


describe('getCartAmountInCents', () => {
    const originalPrestashop = global.prestashop;

    afterEach(() => {
        global.prestashop = originalPrestashop;
    });

    test('returns null when prestashop is undefined', () => {
        delete global.prestashop;
        expect(getCartAmountInCents()).toBeNull();
    });

    test('returns null when prestashop.cart is missing', () => {
        global.prestashop = {};
        expect(getCartAmountInCents()).toBeNull();
    });

    test('returns null when totals are missing', () => {
        global.prestashop = { cart: {} };
        expect(getCartAmountInCents()).toBeNull();
    });

    test('returns amount in cents from total_including_tax', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: '229.76' } } } };
        expect(getCartAmountInCents()).toBe(22976);
    });

    test('falls back to total when total_including_tax is absent', () => {
        global.prestashop = { cart: { totals: { total: { amount: '150.00' } } } };
        expect(getCartAmountInCents()).toBe(15000);
    });

    test('rounds up correctly for 3-decimal amounts', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: '10.999' } } } };
        expect(getCartAmountInCents()).toBe(1100);
    });

    test('rounds down correctly for 3-decimal amounts', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: '10.994' } } } };
        expect(getCartAmountInCents()).toBe(1099);
    });

    test('rounds correctly for amounts with more than 3 decimals', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: '229.7654' } } } };
        expect(getCartAmountInCents()).toBe(22977);
    });

    test('handles numeric (non-string) amount', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: 229.76 } } } };
        expect(getCartAmountInCents()).toBe(22976);
    });

    test('handles numeric integer amount', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: 229 } } } };
        expect(getCartAmountInCents()).toBe(22900);
    });
});
