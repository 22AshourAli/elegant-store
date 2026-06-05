< xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ config('app.url') }}</loc>
        <priority>1.0</priority>
        <changefreq>daily</changefreq>
    </url>
    @foreach($categories as $cat)
    <url>
        <loc>{{ config('app.url') }}/category/{{ $cat->slug }}</loc>
        <priority>0.8</priority>
        <changefreq>weekly</changefreq>
    </url>
    @endforeach
    @foreach($products as $product)
    <url>
        <loc>{{ config('app.url') }}/product/{{ $product->slug }}</loc>
        <priority>0.6</priority>
        <changefreq>weekly</changefreq>
    </url>
    @endforeach
</urlset>
