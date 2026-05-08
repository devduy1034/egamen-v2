{
    "@context": "https://schema.org",
    "@type": "NewsArticle",
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{config('app.asset').$url}}"
    },
    "headline": "{{$name}}",
    "image":{
        "@context": "https://schema.org/",
        "@type": "ImageObject",
        "contentUrl": "{{$image}}",
        "url": "{{$image}}",
        "license": "{{config('app.asset').$url}}",
        "acquireLicensePage": "{{config('app.asset').$url}}",
        "creditText": "{{$name}}",
        "copyrightNotice": "{{$name_author}}",
        "creator":{
            "@type": "Organization",
            "name": "{{$name_author}}"
        }
    },
    "datePublished": "{{date('c', $ngaytao)}}",
    "dateModified": "{{date('c', $ngaysua)}}",
    "author":{
        "@type": "Organization",
        "name": "{{$name_author}}",
        "url": "{{config('app.asset').$url}}"
    },
    "publisher":{
        "@type": "Organization",
        "name": "{{$name_author}}",
        "logo":{
            "@type": "ImageObject",
            "url": "{{$logo_schema}}"
        }
    }
}