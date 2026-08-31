@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Media Gallery</h1>
        <button type="button" @click="$dispatch('open-upload-modal')" class="bg-navy hover:bg-opacity-90 text-white text-sm font-bold py-2 px-6 rounded shadow transition">
            Upload New File
        </button>
    </div>

    <!-- Upload Modal -->
    <div x-data="{ open: false, uploading: false, progress: 0 }" 
         @open-upload-modal.window="open = true" 
         x-show="open" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Upload File</h3>
                            <div class="mt-4">
                                <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-gray-50 hover:bg-gray-100 transition relative">
                                    <div class="space-y-1 text-center" x-show="!uploading">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-cyan hover:text-cyan-dark focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-cyan">
                                                <span>Select a file</span>
                                                <input id="file-upload" name="file-upload" type="file" class="sr-only" @change="uploadFile($event)">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, PDF, ZIP up to 10MB</p>
                                    </div>
                                    <div x-show="uploading" class="text-center py-4 w-full">
                                        <p class="text-sm font-medium text-gray-600 mb-2">Uploading...</p>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-cyan h-2.5 rounded-full" :style="`width: ${progress}%`"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Grid -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($media->count() > 0)
            <div class="p-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                @foreach($media as $item)
                    <div class="group relative bg-gray-50 rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition">
                        <!-- Preview Area -->
                        <div class="aspect-w-10 aspect-h-7 bg-gray-200 flex items-center justify-center overflow-hidden">
                            @if(str_starts_with($item->mime_type, 'image/'))
                                <img src="{{ $item->url }}" alt="{{ $item->name }}" class="object-cover w-full h-full">
                            @else
                                <div class="flex flex-col items-center justify-center p-4">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-xs font-semibold text-gray-500 mt-2 uppercase">{{ pathinfo($item->name, PATHINFO_EXTENSION) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Actions & Details -->
                        <div class="p-3">
                            <p class="text-xs font-medium text-gray-900 truncate" title="{{ $item->name }}">{{ $item->name }}</p>
                            <p class="text-[10px] text-gray-500">{{ $item->human_readable_size }} &bull; {{ $item->created_at->format('M d, Y') }}</p>
                            
                            <div class="mt-3 flex justify-between items-center opacity-0 group-hover:opacity-100 transition">
                                <button type="button" @click="copyToClipboard('{{ $item->url }}')" class="text-xs font-semibold text-cyan hover:text-cyan-dark flex items-center" title="Copy Link">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                    Copy
                                </button>
                                
                                <form action="{{ route('admin.media.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this file? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700 flex items-center" title="Delete">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $media->links() }}
            </div>
        @else
            <div class="text-center py-20">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No media uploaded</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by uploading a new file.</p>
                <div class="mt-6">
                    <button type="button" @click="$dispatch('open-upload-modal')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-navy hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Upload File
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Component logic is mostly in Alpine, but here is the upload logic and copy logic
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Link copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            alert('Failed to copy link.');
        });
    }

    function uploadFile(event) {
        let file = event.target.files[0];
        if (!file) return;

        let formData = new FormData();
        formData.append('file', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Look up Alpine component data
        let alpineComponent = event.target.closest('[x-data]');
        let componentData = alpineComponent ? alpineComponent.__x.$data : null;
        
        if (componentData) {
            componentData.uploading = true;
            componentData.progress = 0;
        }

        let xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route('admin.media.store') }}', true);
        
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable && componentData) {
                let percentComplete = (e.loaded / e.total) * 100;
                componentData.progress = percentComplete;
            }
        };

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                // Success, reload page to show new media
                window.location.reload();
            } else {
                alert('Upload failed: ' + xhr.responseText);
                if (componentData) {
                    componentData.uploading = false;
                    componentData.progress = 0;
                }
            }
        };

        xhr.onerror = function() {
            alert('Upload failed.');
            if (componentData) {
                componentData.uploading = false;
                componentData.progress = 0;
            }
        };

        xhr.send(formData);
    }
</script>
@endsection
