var widgets = Alma.Widgets.initialize(
    '<MERCHANT ID>',
    Alma.ApiMode.TEST,
)

widgets.add(Alma.Widgets.PaymentPlans, {
    container: '#alma-widget',
    purchaseAmount: 45000,
    locale: 'fr',
    hideIfNotEligible: false,
    plans: [
        {
            installmentsCount: 1,
            deferredDays: 30,
            minAmount: 5000,
            maxAmount: 50000,
        },
        {
            installmentsCount: 3,
            minAmount: 5000,
            maxAmount: 50000,
        },
    ],
})
