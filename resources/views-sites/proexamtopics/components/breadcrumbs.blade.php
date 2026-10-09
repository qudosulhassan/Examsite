@props(['links' => []])

@php
    $schemaList = [];
    $position = 1;
    foreach ($links as $link) {
        $schemaList[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $link['name'],
            'item' => $link['url'] ? url($link['url']) : url()->current(),
        ];
        $position++;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $schemaList,
    ];
@endphp

<!-- Visual Breadcrumbs -->
<nav class="flex text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        @foreach($links as $index => $link)
            <li class="inline-flex items-center">
                @if(!$loop->first)
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                @endif
                @if($link['url'])
                    <a href="{{ url($link['url']) }}" class="inline-flex items-center font-medium text-gray-700 hover:text-blue-600 transition-colors">
                        @if($loop->first)
                            <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                            </svg>
                        @endif
                        {{ $link['name'] }}
                    </a>
                @else
                    <span class="inline-flex items-center font-medium text-gray-400">
                        {{ $link['name'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

<!-- JSON-LD Breadcrumbs -->
<script type="application/ld+json">
    {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
