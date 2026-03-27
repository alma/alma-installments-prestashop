const { initAlmaWidget, getCartAmountInCents } = require('../alma-widget');

const mockWidgetConfig = {
    purchaseAmount: 22976,
    containerId: '#alma-widget-cart',
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

const mockJQuery = ({ cartExists = false, footerExists = false, config = mockWidgetConfig } = {}) => {
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
        const $ = mockJQuery({ cartExists: true, config: { ...mockWidgetConfig, mode: 'live' } });
        initAlmaWidget($, mockAlma);
        expect(mockAlma.Widgets.initialize).toHaveBeenCalledWith('merchant_123', 'live');
    });

    test('initialize Alma with #alma-widget-ShoppingCartFooter if no cart', () => {
        const $ = mockJQuery({ footerExists: true });
        initAlmaWidget($, mockAlma);
        expect(mockAlma.Widgets.initialize).toHaveBeenCalledWith('merchant_123', 'test');
    });

    test('use #alma-widget-cart priority if both exist', () => {
        const footerConfig = { ...mockWidgetConfig, merchantId: 'merchant_footer' };
        const cartConfig = { ...mockWidgetConfig, merchantId: 'merchant_cart' };
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

    test('call widgets.add with goog parameters', () => {
        const $ = mockJQuery({ cartExists: true });
        const widgets = initAlmaWidget($, mockAlma);
        expect(widgets.add).toHaveBeenCalledWith('PaymentPlans', expect.objectContaining({
            purchaseAmount: 22976,
            locale: 'en',
        }));
    });

    test('parse plans if it is a string', () => {
        const config = { ...mockWidgetConfig, plans: '[{"installmentsCount":3}]' };
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

    test('rounds correctly for floating point amounts', () => {
        global.prestashop = { cart: { totals: { total_including_tax: { amount: '10.999' } } } };
        expect(getCartAmountInCents()).toBe(1100);
    });
});
