(function ($) {
    $(function () {
        const subscriptionData = {
            orderId: '1234567',
            orderReference: 'd4cb55a9',
            mode: 'test',
            orderDate: '2024-01-05',
            firstName: 'Jane',
            lastName: 'Doe',
            cmsSubscriptions:
                [
                    {
                        id: 1,
                        productName: 'Product 1',
                        insuranceName: 'panne + casse + vol',
                        status: 'Active',
                        productPrice: 39554,
                        insurancePrice: 5130,
                        isRefunded: false,
                        motifOfCancellation: '',
                        dateOfCancellation: '',
                        externalSubcriptionId: '1451bfc6',
                        urlOfProductImg: 'https://example.com/product-image.jpg',
                    },
                    {
                        id: 2,
                        productName: 'Product 2',
                        insuranceName: 'vol + panne',
                        status: 'Voided',
                        productPrice: 21674,
                        insurancePrice: 12299,
                        isRefunded: false,
                        motifOfCancellation: '',
                        dateOfCancellation: '',
                        externalSubcriptionId: '911483ed',
                        urlOfProductImg: 'https://example.com/product-image.jpg',
                    },
                    {
                        id: 3,
                        productName: 'Product 3',
                        insuranceName: 'panne',
                        status: 'Voided',
                        productPrice: 32283,
                        insurancePrice: 8098,
                        isRefunded: false,
                        motifOfCancellation: 'Damaged on Arrival',
                        dateOfCancellation: '2024-01-26',
                        externalSubcriptionId: 'fe07fa87',
                        urlOfProductImg: 'https://example.com/product-image.jpg',
                    },
                    {
                        id: 4,
                        productName: 'Product 4',
                        insuranceName: 'panne',
                        status: 'Active',
                        productPrice: 33278,
                        insurancePrice: 8993,
                        isRefunded: false,
                        motifOfCancellation: 'Changed Mind',
                        dateOfCancellation: '2024-01-26',
                        externalSubcriptionId: '2c1dec77',
                        urlOfProductImg: 'https://example.com/product-image.jpg',
                    },
                    {
                        id: 5,
                        productName: 'Product 5',
                        insuranceName: 'vol',
                        status: 'Active',
                        productPrice: 39077,
                        insurancePrice: 11648,
                        isRefunded: false,
                        motifOfCancellation: '',
                        dateOfCancellation: '',
                        externalSubcriptionId: '55e0e0d2',
                        urlOfProductImg: 'https://example.com/product-image.jpg',
                    },
                ]
        }

            $('#alma_config_form_submit_btn').on('click', function(event) {
                event.preventDefault();
                getSubscriptionDatafromCms(subscriptionData)
            });

    })
})(jQuery);