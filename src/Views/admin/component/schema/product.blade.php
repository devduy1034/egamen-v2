{
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "{{$name}}",
    "image": [
        @foreach ($image as $k => $v)
            {
                "@context": "https://schema.org/",
                "@type": "ImageObject",
                "contentUrl": "{{$v}}",
                "url": "{{$v}}",
                "license": "{{config('app.asset').$url}}",
                "acquireLicensePage": "{{config('app.asset').$url}}",
                "creditText": "{{$name}}",
                "copyrightNotice": "{{$name_author}}",
                "creator": {
                "@type": "Organization",
                "name": "{{$name_author}}"
            }@if($k < count($image) - 1) ',' @endif
        }
        @endforeach
    ],
    "description": "{{$description}}",
    "sku": "SP{{$id_pro}}",
    "mpn": "{{$code_pro}}",
    "brand": {
        "@type": "Brand",
        "name": "{{((!empty($name_brand)) ? $name_brand : $name_author)}}"
    },
    "review": {
    "@type": "Review",
        "reviewRating": {
            "@type": "Rating",
            "ratingValue": "5",
            "bestRating": "5"
        },
        "author": {
            "@type": "Person",
            "name": "{{$name_author}}"
        }
    },
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.4",
        "reviewCount": "89"
    },
    "offers": {
    "@type": "Offer",
        "url": "{{config('app.asset').$url}}",
        "priceCurrency": "VND",
        "priceValidUntil": "2099-11-20T00:00:00+07:00",
        "price": "{{$price}}",
        "itemCondition": "https://schema.org/NewCondition",
        "availability": "https://schema.org/InStock",
        "hasMerchantReturnPolicy": {
        "@type": "MerchantReturnPolicy",
            "applicableCountry": "Vi",
            "returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",
            "merchantReturnDays": "7",
            "returnMethod": "https://schema.org/ReturnByMail",
            "returnFees": "https://schema.org/FreeReturn"
        },
        "priceSpecification": {
            "@type": "PriceSpecification",
            "priceCurrency": "VND",
            "price": "{{$price}}",
            "minPrice": "{{$price}}",
            "maxPrice": "{{$price}}"
        },
        "shippingDetails": {
            "@type": "OfferShippingDetails",
            "shippingRate": {
                "@type": "MonetaryAmount",
                "value": "0",
                "currency": "VND"
            },
            "shippingDestination": {
                "@type": "DefinedRegion",
                "addressCountry": "VN"
            },
            "deliveryTime": {
                "@type": "ShippingDeliveryTime",
                "handlingTime": {
                "@type": "QuantitativeValue",
                    "minValue": "1",
                    "maxValue": "7",
                    "unitCode": "DAY"
                },
                "transitTime": {
                    "@type": "QuantitativeValue",
                    "minValue": "1",
                    "maxValue": "7",
                    "unitCode": "DAY"
                }
            }
        }
    }
}