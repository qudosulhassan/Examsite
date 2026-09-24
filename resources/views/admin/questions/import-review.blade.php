@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="reviewBatchComponent()">

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-gray-800">Batch Review: {{ $batch->uuid }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-navy text-white">
                    {{ $batch->format_detected }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Source: <strong>{{ $batch->filename }}</strong> &bull; Uploaded {{ $batch->created_at->diffForHumans() }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('admin.questions.import-cancel-batch', $batch->uuid) }}" method="POST" onsubmit="return confirm('Cancel and delete this import batch?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-xs font-bold rounded shadow-sm transition">
                    Cancel
                </button>
            </form>

            <a href="{{ route('admin.questions.import-error-report', $batch->uuid) }}" class="px-3.5 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-xs font-bold rounded shadow-sm transition flex items-center">
                <svg class="h-4 w-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Report
            </a>

            <!-- Bulk Action: Select Ready Only -->
            <button type="button" @click="selectReadyOnly()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow transition flex items-center space-x-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Select Ready (<span x-text="readyCount"></span>)</span>
            </button>

            <!-- Import Selected -->
            <button @click="startImport()" :disabled="selectedIds.length === 0"
                    class="px-5 py-2 bg-navy hover:bg-opacity-95 text-white text-xs font-bold rounded shadow transition disabled:opacity-50 flex items-center space-x-2">
                <span>Import Selected (<span x-text="selectedIds.length"></span>)</span>
            </button>
        </div>
    </div>

    @if(!empty($batch->options['pdf_diagnostics']))
        @php $diag = $batch->options['pdf_diagnostics']; @endphp
        <!-- PDF Production Diagnostics Panel -->
        <div class="bg-navy text-white rounded-lg p-4 shadow-sm border border-navy/20 space-y-3">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs uppercase font-bold text-gray-300">Document Layout:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-cyan text-navy">
                        {{ $diag['document_classification'] ?? 'TEXT_BASED' }}
                    </span>
                    <span class="text-xs uppercase font-bold text-gray-300 ml-2">Extraction Quality:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold {{ ($diag['quality_score'] ?? 80) >= 85 ? 'bg-emerald-500 text-white' : (($diag['quality_score'] ?? 80) >= 70 ? 'bg-blue-500 text-white' : 'bg-amber-500 text-black') }}">
                        {{ $diag['quality_score'] ?? 80 }}% &bull; {{ $diag['quality_tier'] ?? 'GOOD' }}
                    </span>
                </div>
                <div class="text-xs text-gray-300">
                    Average Confidence: <strong>{{ $diag['average_confidence'] ?? 85 }}%</strong> &bull; Visual Assets: <strong>{{ $diag['images_detected'] ?? 0 }}</strong>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 pt-2 border-t border-white/10 text-xs">
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase">Pages Analyzed</span>
                    <span class="font-bold text-sm text-white">{{ $diag['page_count'] ?? 1 }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase">Ready to Import</span>
                    <span class="font-bold text-sm text-emerald-400">{{ $diag['ready_count'] ?? 0 }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase">Review Required</span>
                    <span class="font-bold text-sm text-amber-400">{{ $diag['review_required_count'] ?? 0 }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase">High Confidence (85%+)</span>
                    <span class="font-bold text-sm text-emerald-400">{{ $diag['high_confidence_count'] ?? 0 }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase">Medium (65-84%)</span>
                    <span class="font-bold text-sm text-yellow-400">{{ $diag['medium_confidence_count'] ?? 0 }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase">Images Extracted</span>
                    <span class="font-bold text-sm text-cyan">{{ $diag['images_detected'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <!-- Total -->
        <div class="bg-white border border-gray-250 p-4 rounded-lg shadow-sm">
            <div class="text-xs font-bold text-gray-400 uppercase">Total Detected</div>
            <div class="text-2xl font-bold text-gray-800 mt-1">{{ $batch->total_questions }}</div>
        </div>

        <!-- Ready -->
        <div @click="activeFilter = 'ready'" class="bg-white border border-gray-250 p-4 rounded-lg shadow-sm cursor-pointer hover:border-emerald-500 transition"
             :class="activeFilter === 'ready' ? 'ring-2 ring-emerald-500' : ''">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-600 uppercase">✓ Ready</span>
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-2xl font-bold text-emerald-700 mt-1" x-text="readyCount"></div>
        </div>

        <!-- Review Required -->
        <div @click="activeFilter = 'review_required'" class="bg-white border border-gray-250 p-4 rounded-lg shadow-sm cursor-pointer hover:border-amber-400 transition"
             :class="activeFilter === 'review_required' ? 'ring-2 ring-amber-400' : ''">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-600 uppercase">⚠ Needs Review</span>
                <span class="h-2 w-2 rounded-full bg-amber-400"></span>
            </div>
            <div class="text-2xl font-bold text-amber-700 mt-1" x-text="reviewRequiredCount"></div>
        </div>

        <!-- Errors / Failed -->
        <div @click="activeFilter = 'failed'" class="bg-white border border-gray-250 p-4 rounded-lg shadow-sm cursor-pointer hover:border-rose-500 transition"
             :class="activeFilter === 'failed' ? 'ring-2 ring-rose-500' : ''">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-600 uppercase">❌ Failed</span>
                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
            </div>
            <div class="text-2xl font-bold text-rose-700 mt-1" x-text="failedCount"></div>
        </div>

        <!-- Duplicates -->
        <div @click="activeFilter = 'duplicate'" class="bg-white border border-gray-250 p-4 rounded-lg shadow-sm cursor-pointer hover:border-orange transition"
             :class="activeFilter === 'duplicate' ? 'ring-2 ring-orange' : ''">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-orange uppercase">⚠ Duplicates</span>
                <span class="h-2 w-2 rounded-full bg-orange"></span>
            </div>
            <div class="text-2xl font-bold text-orange mt-1">{{ $batch->duplicate_count }}</div>
        </div>
    </div>

    <!-- Duplicate Alert -->
    <div x-show="items.some(i => i.duplicate_status !== 'none')" class="bg-orange/10 border border-orange/30 p-3 rounded-lg flex items-center justify-between text-xs text-orange">
        <div class="flex items-center space-x-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span><strong>Duplicate Questions Detected:</strong> You can replace existing database questions or import alongside them.</span>
        </div>
        <label class="flex items-center space-x-2 cursor-pointer font-bold text-gray-800">
            <input type="checkbox" x-model="replaceDuplicates" class="rounded border-gray-300 text-orange focus:ring-orange h-4 w-4">
            <span>Replace Existing Matched Questions</span>
        </label>
    </div>

    <!-- Filter Bar & Search -->
    <div class="bg-white border border-gray-250 rounded-lg p-4 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
        <!-- Status Tabs -->
        <div class="flex flex-wrap gap-2 text-xs font-bold">
            <button @click="activeFilter = 'all'" :class="activeFilter === 'all' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-3 py-1.5 rounded transition">
                All (<span x-text="items.length"></span>)
            </button>
            <button @click="activeFilter = 'ready'" :class="activeFilter === 'ready' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-emerald-700 hover:bg-gray-200'" class="px-3 py-1.5 rounded transition">
                Ready (<span x-text="readyCount"></span>)
            </button>
            <button @click="activeFilter = 'review_required'" :class="activeFilter === 'review_required' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-amber-700 hover:bg-gray-200'" class="px-3 py-1.5 rounded transition">
                Needs Review (<span x-text="reviewRequiredCount"></span>)
            </button>
            <button @click="activeFilter = 'failed'" :class="activeFilter === 'failed' ? 'bg-rose-600 text-white' : 'bg-gray-100 text-rose-700 hover:bg-gray-200'" class="px-3 py-1.5 rounded transition">
                Failed (<span x-text="failedCount"></span>)
            </button>
            <button @click="activeFilter = 'duplicate'" :class="activeFilter === 'duplicate' ? 'bg-orange text-white' : 'bg-gray-100 text-orange hover:bg-gray-200'" class="px-3 py-1.5 rounded transition">
                Duplicates (<span x-text="items.filter(i => i.validation_status === 'duplicate').length"></span>)
            </button>
        </div>

        <!-- Controls -->
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <!-- Topic Filter -->
            <select x-model="topicFilter" class="border-gray-300 rounded text-xs px-2.5 py-1.5 focus:border-cyan focus:ring-cyan">
                <option value="">All Topics</option>
                <template x-for="top in availableTopics" :key="top">
                    <option :value="top" x-text="top"></option>
                </template>
            </select>

            <!-- Type Filter -->
            <select x-model="typeFilter" class="border-gray-300 rounded text-xs px-2.5 py-1.5 focus:border-cyan focus:ring-cyan">
                <option value="">All Question Types</option>
                <option value="single_choice">Single Choice</option>
                <option value="multiple_choice">Multiple Choice</option>
                <option value="hotspot">Hotspot / Dropdown</option>
                <option value="drag_drop">Drag & Drop</option>
                <option value="yes_no">Yes / No</option>
                <option value="case_study">Case Study</option>
                <option value="simulation">Simulation</option>
                <option value="unknown">Unknown</option>
            </select>

            <!-- Search -->
            <input type="text" x-model="searchQuery" placeholder="Search question..."
                   class="border-gray-300 rounded text-xs px-3 py-1.5 focus:border-cyan focus:ring-cyan w-40">
        </div>
    </div>

    <!-- Questions Review Table -->
    <div class="bg-white border border-gray-250 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                <thead class="bg-gray-50 text-gray-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-3 w-8">
                            <input type="checkbox" @click="selectAllFiltered()"
                                   :checked="filteredItems.filter(i => i.normalized_data.readiness_status !== 'FAILED').length > 0 && filteredItems.filter(i => i.normalized_data.readiness_status !== 'FAILED').every(i => selectedIds.includes(i.id))"
                                   class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        </th>
                        <th class="p-3 w-12 text-center">#</th>
                        <th class="p-3 w-28">Readiness</th>
                        <th class="p-3">Question Preview</th>
                        <th class="p-3 w-28">Topic</th>
                        <th class="p-3 w-32">Type</th>
                        <th class="p-3 w-28">Exhibits</th>
                        <th class="p-3 w-36">Issues</th>
                        <th class="p-3 w-32 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 bg-white">
                    <template x-for="item in filteredItems" :key="item.id">
                        <tr class="hover:bg-gray-50 transition" :class="item.normalized_data.readiness_status === 'FAILED' ? 'bg-rose-50/40' : (item.validation_status === 'duplicate' ? 'bg-orange/5' : '')">
                            <!-- Checkbox -->
                            <td class="p-3">
                                <input type="checkbox" :value="item.id" x-model="selectedIds" :disabled="item.normalized_data.readiness_status === 'FAILED'"
                                       class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4 disabled:opacity-40">
                            </td>

                            <!-- Index -->
                            <td class="p-3 text-center font-bold text-gray-400" x-text="item.source_index"></td>

                            <!-- Readiness Badge -->
                            <td class="p-3">
                                <template x-if="item.normalized_data.readiness_status === 'READY'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        ✓ Ready
                                    </span>
                                </template>
                                <template x-if="item.normalized_data.readiness_status === 'REVIEW_REQUIRED'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                                        ⚠ Needs Review
                                    </span>
                                </template>
                                <template x-if="item.normalized_data.readiness_status === 'FAILED'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800">
                                        ❌ Failed
                                    </span>
                                </template>
                            </td>

                            <!-- Question Text & Source Pages -->
                            <td class="p-3 font-medium text-gray-800">
                                <div class="line-clamp-2" x-text="item.normalized_data.question_text || '(Missing question text)'"></div>
                                <template x-if="item.normalized_data.source_reference?.page_start">
                                    <div class="mt-1 flex items-center space-x-2 text-[10px]">
                                        <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-semibold"
                                              x-text="'Pages ' + item.normalized_data.source_reference.page_start + (item.normalized_data.source_reference.page_start != item.normalized_data.source_reference.page_end ? '–' + item.normalized_data.source_reference.page_end : '')"></span>
                                        <template x-if="item.normalized_data.source_reference.confidence_score">
                                            <span class="px-1.5 py-0.5 rounded font-bold"
                                                  :class="item.normalized_data.source_reference.confidence_score >= 85 ? 'bg-emerald-100 text-emerald-700' : (item.normalized_data.source_reference.confidence_score >= 65 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-700')"
                                                  x-text="item.normalized_data.source_reference.confidence_score + '% Confidence'"></span>
                                        </template>
                                    </div>
                                </template>
                            </td>

                            <!-- Topic -->
                            <td class="p-3 text-gray-600">
                                <span class="font-bold" x-text="item.normalized_data.topic || 'Topic 1'"></span>
                                <span class="text-[10px] text-gray-400 block" x-text="'Q#' + (item.normalized_data.local_question_number || item.source_index)"></span>
                            </td>

                            <!-- Type Badge -->
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold"
                                      :class="{
                                          'bg-blue-100 text-blue-800': item.normalized_data.question_type === 'single_choice',
                                          'bg-purple-100 text-purple-800': item.normalized_data.question_type === 'multiple_choice',
                                          'bg-cyan/20 text-navy font-bold': item.normalized_data.question_type === 'hotspot',
                                          'bg-emerald-100 text-emerald-800': item.normalized_data.question_type === 'drag_drop',
                                          'bg-gray-100 text-gray-700': item.normalized_data.question_type === 'case_study' || item.normalized_data.question_type === 'unknown'
                                      }"
                                      x-text="formatTypeName(item.normalized_data.question_type)"></span>
                            </td>

                            <!-- Exhibits Pill -->
                            <td class="p-3 text-[11px] text-gray-600">
                                <template x-if="item.normalized_data.question_exhibits && item.normalized_data.question_exhibits.length">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-cyan/10 text-cyan text-[10px] font-bold">
                                        🖼 <span class="ml-1" x-text="item.normalized_data.question_exhibits.length + ' visual(s)'"></span>
                                    </span>
                                </template>
                                <template x-if="!item.normalized_data.question_exhibits || !item.normalized_data.question_exhibits.length">
                                    <span class="text-gray-400">—</span>
                                </template>
                            </td>

                            <!-- Compact Issues Badge -->
                            <td class="p-3 text-[11px]">
                                <template x-if="item.validation_errors && item.validation_errors.length">
                                    <span class="px-2 py-0.5 rounded font-bold bg-rose-100 text-rose-700 text-[10px]"
                                          x-text="'❌ ' + item.validation_errors.length + ' error(s)'"></span>
                                </template>
                                <template x-if="(!item.validation_errors || !item.validation_errors.length) && item.validation_warnings && item.validation_warnings.length">
                                    <span class="px-2 py-0.5 rounded font-bold bg-amber-100 text-amber-800 text-[10px]"
                                          x-text="'⚠ ' + item.validation_warnings.length + ' issue(s)'"></span>
                                </template>
                                <template x-if="(!item.validation_errors || !item.validation_errors.length) && (!item.validation_warnings || !item.validation_warnings.length)">
                                    <span class="text-emerald-600 font-bold text-[10px]">✓ All Clear</span>
                                </template>
                            </td>

                            <!-- Actions -->
                            <td class="p-3 text-right space-x-1.5 whitespace-nowrap">
                                <button type="button" @click.stop="openReview(item, 'learner_preview')" class="px-2.5 py-1 text-xs font-bold text-cyan bg-cyan/10 hover:bg-cyan/20 rounded transition">
                                    Preview
                                </button>
                                <button type="button" @click.stop="openReview(item, 'source_vs_extracted')" class="px-2.5 py-1 text-xs font-bold text-navy bg-navy/10 hover:bg-navy/20 rounded transition">
                                    Verify
                                </button>
                                <button type="button" @click.stop="openReview(item, 'admin_review')" class="px-2.5 py-1 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded transition">
                                    Review
                                </button>
                                <button type="button" @click.stop="openReview(item, 'edit')" class="px-2.5 py-1 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded transition">
                                    Edit
                                </button>
                                <a :href="'/admin/questions/import/item/' + item.id + '/preview'" target="_blank" title="Open full-page preview in new tab" class="px-2 py-1 text-xs font-bold text-gray-500 hover:text-cyan border border-gray-200 hover:border-cyan rounded transition inline-flex items-center">
                                    <span>↗</span>
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- DETAILED REVIEW & PREVIEW MODAL -->
    <div x-show="selectedItem !== null" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop Overlay -->
        <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity" @click="selectedItem = null"></div>

        <!-- Center Positioner Container -->
        <div class="min-h-full flex items-center justify-center p-4 text-center sm:p-6 relative z-10 pointer-events-none">
            
            <!-- Modal Card -->
            <div class="relative w-full max-w-4xl bg-white rounded-xl shadow-2xl text-left overflow-hidden border border-gray-250 z-20 pointer-events-auto"
                 @click.stop>
                
                <!-- Modal Header -->
                <div class="bg-navy px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center space-x-3">
                        <span class="font-bold text-base" x-text="'Question #' + (selectedItem ? selectedItem.source_index : '') + ' (' + (selectedItem?.normalized_data?.topic || 'Topic 1') + ')'"></span>
                        <template x-if="selectedItem">
                            <span class="px-2 py-0.5 rounded text-xs font-bold"
                                  :class="selectedItem?.normalized_data?.readiness_status === 'READY' ? 'bg-emerald-500 text-white' : (selectedItem?.normalized_data?.readiness_status === 'REVIEW_REQUIRED' ? 'bg-amber-500 text-black' : 'bg-rose-500 text-white')"
                                  x-text="selectedItem?.normalized_data?.readiness_status || ''"></span>
                        </template>
                    </div>

                    <!-- Mode Toggle & Actions -->
                    <div class="flex items-center space-x-2">
                        <button type="button" @click="modalMode = 'learner_preview'" :class="modalMode === 'learner_preview' ? 'bg-cyan text-navy font-bold' : 'text-gray-300 hover:text-white'" class="px-3 py-1 rounded text-xs transition">
                            Learner Preview
                        </button>
                        <button type="button" @click="modalMode = 'source_vs_extracted'" :class="modalMode === 'source_vs_extracted' ? 'bg-cyan text-navy font-bold' : 'text-gray-300 hover:text-white'" class="px-3 py-1 rounded text-xs transition">
                            Source vs Extracted
                        </button>
                        <button type="button" @click="modalMode = 'admin_review'" :class="modalMode === 'admin_review' ? 'bg-cyan text-navy font-bold' : 'text-gray-300 hover:text-white'" class="px-3 py-1 rounded text-xs transition">
                            Admin Review
                        </button>
                        <button type="button" @click="modalMode = 'edit'" :class="modalMode === 'edit' ? 'bg-cyan text-navy font-bold' : 'text-gray-300 hover:text-white'" class="px-3 py-1 rounded text-xs transition">
                            Edit
                        </button>
                        <a :href="'/admin/questions/import/item/' + (selectedItem ? selectedItem.id : '') + '/' + (modalMode === 'edit' ? 'edit' : (modalMode === 'admin_review' ? 'review' : 'preview'))" target="_blank" title="Open Standalone Page" class="text-cyan hover:underline text-xs ml-2 font-bold flex items-center">
                            <span>Full Page ↗</span>
                        </a>
                        <button type="button" @click="selectedItem = null" class="text-gray-400 hover:text-white text-lg ml-2 font-bold">&times;</button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 max-h-[75vh] overflow-y-auto space-y-6">

                    <!-- 1. LEARNER PREVIEW MODE -->
                    <div x-show="modalMode === 'learner_preview'" class="space-y-6">
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-800 flex items-center justify-between">
                            <span><strong>Learner View Simulation:</strong> Answers, explanations, and answer-level screenshots are hidden.</span>
                            <span class="text-gray-500 font-bold" x-text="formatTypeName(selectedItem?.normalized_data?.question_type || '')"></span>
                        </div>

                        <!-- Question Prompt -->
                        <div class="bg-gray-50 p-4 border border-gray-200 rounded-lg text-sm text-gray-900 font-medium leading-relaxed">
                            <div class="whitespace-pre-line" x-text="selectedItem?.normalized_data?.question_text || ''"></div>
                        </div>

                        <!-- Question Visual Exhibits -->
                        <template x-if="selectedItem?.normalized_data?.question_exhibits && selectedItem.normalized_data.question_exhibits.length">
                            <div class="space-y-3">
                                <h4 class="text-xs font-bold text-gray-400 uppercase">Question Exhibit(s)</h4>
                                <div class="grid grid-cols-1 gap-3">
                                    <template x-for="img in (selectedItem?.normalized_data?.question_exhibits || [])" :key="img.url">
                                        <div class="border border-gray-200 rounded p-2 bg-gray-50">
                                            <img :src="img.url" :alt="img.caption" class="max-h-96 mx-auto rounded shadow-sm">
                                            <span class="text-[10px] text-gray-500 block text-center mt-1" x-text="img.caption"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Standard MCQ Options -->
                        <template x-if="selectedItem && (selectedItem.normalized_data?.question_type === 'single_choice' || selectedItem.normalized_data?.question_type === 'multiple_choice' || selectedItem.normalized_data?.question_type === 'yes_no')">
                            <div class="space-y-2">
                                <template x-for="opt in (selectedItem?.normalized_data?.options || [])" :key="opt.key">
                                    <div class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg bg-white hover:border-cyan cursor-pointer transition">
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 text-gray-700" x-text="opt.key"></span>
                                        <span class="text-sm text-gray-800" x-text="opt.text"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Hotspot Interactive Dropdowns -->
                        <template x-if="selectedItem && selectedItem.normalized_data?.question_type === 'hotspot' && selectedItem.normalized_data?.answer_area?.boxes?.length">
                            <div class="space-y-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <h4 class="text-xs font-bold text-gray-500 uppercase">Answer Area Dropdown Selections</h4>
                                <div class="space-y-2">
                                    <template x-for="box in (selectedItem?.normalized_data?.answer_area?.boxes || [])" :key="box.box_number || box.label">
                                        <div class="flex items-center justify-between p-2 bg-white border border-gray-200 rounded">
                                            <span class="font-bold text-xs text-gray-700" x-text="box.label"></span>
                                            <select class="text-xs border-gray-300 rounded focus:border-cyan">
                                                <option value="">-- Select Option --</option>
                                                <option x-text="box.correct"></option>
                                            </select>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Drag Drop Sequence -->
                        <template x-if="selectedItem && selectedItem.normalized_data?.question_type === 'drag_drop' && selectedItem.normalized_data?.answer_area?.steps?.length">
                            <div class="space-y-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <h4 class="text-xs font-bold text-gray-500 uppercase">Answer Area (Select & Place)</h4>
                                <div class="space-y-2">
                                    <template x-for="step in (selectedItem?.normalized_data?.answer_area?.steps || [])" :key="step.step_number || step.label">
                                        <div class="p-2.5 bg-white border border-dashed border-gray-300 rounded flex items-center space-x-2 text-xs">
                                            <span class="font-bold text-cyan" x-text="step.label + ':'"></span>
                                            <span class="text-gray-700" x-text="step.text"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- 2. SOURCE VS EXTRACTED COMPARISON VIEW -->
                    <div x-show="modalMode === 'source_vs_extracted'" class="space-y-6">
                        <!-- Field Status Verification Matrix -->
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Field-Level Verification Matrix</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                <template x-for="(status, field) in (selectedItem?.normalized_data?.field_statuses || {})" :key="field">
                                    <div class="p-2.5 rounded border flex items-center justify-between"
                                         :class="status === 'verified' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : (status === 'review' ? 'bg-amber-50 border-amber-200 text-amber-800' : (status === 'failed' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-gray-50 border-gray-200 text-gray-500'))">
                                         <span class="font-bold capitalize" x-text="field.replace('_status', '').replace('_', ' ')"></span>
                                         <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase"
                                               :class="status === 'verified' ? 'bg-emerald-200 text-emerald-900' : (status === 'review' ? 'bg-amber-200 text-amber-900' : (status === 'failed' ? 'bg-rose-200 text-rose-900' : 'bg-gray-200 text-gray-700'))"
                                               x-text="status"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Discrepancy Alert Cards -->
                        <template x-if="selectedItem?.normalized_data?.discrepancies?.length">
                            <div class="space-y-3">
                                <h4 class="text-xs font-bold text-rose-700 uppercase tracking-wide">Discrepancy Inspections</h4>
                                <template x-for="disc in (selectedItem?.normalized_data?.discrepancies || [])" :key="disc.code">
                                    <div class="p-4 border rounded-lg space-y-2"
                                         :class="disc.severity === 'critical' ? 'bg-rose-50 border-rose-300 text-rose-900' : 'bg-amber-50 border-amber-300 text-amber-900'">
                                        <div class="flex items-center justify-between text-xs font-bold">
                                            <span x-text="(disc.severity === 'critical' ? '❌ ' : '⚠ ') + disc.message"></span>
                                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-mono" x-text="disc.code"></span>
                                        </div>
                                        <template x-if="disc.source || disc.extracted">
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-2 border-t text-xs font-mono"
                                                 :class="disc.severity === 'critical' ? 'border-rose-200' : 'border-amber-200'">
                                                <div class="bg-white/80 p-2 rounded">
                                                    <span class="text-gray-500 font-bold block text-[10px]">SOURCE EVIDENCE</span>
                                                    <span class="text-gray-800" x-text="disc.source || '—'"></span>
                                                </div>
                                                <div class="bg-white/80 p-2 rounded">
                                                    <span class="text-gray-500 font-bold block text-[10px]">PARSED / EXTRACTED</span>
                                                    <span class="text-gray-800" x-text="disc.extracted || '—'"></span>
                                                </div>
                                                <div class="bg-white/80 p-2 rounded">
                                                    <span class="text-gray-500 font-bold block text-[10px]">DIFFERENCE</span>
                                                    <span class="font-bold" :class="disc.severity === 'critical' ? 'text-rose-700' : 'text-amber-700'" x-text="disc.difference || 'Mismatch'"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Answer Comparison Section -->
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Answer Integrity Verification</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg space-y-1">
                                    <span class="font-bold text-gray-500 uppercase text-[10px] block">Source Document Statement</span>
                                    <pre class="bg-white p-2.5 rounded border text-xs font-mono text-gray-800 whitespace-pre-wrap"
                                         x-text="selectedItem?.raw_data?.debug_info?.raw_answer_statement || '(No explicit answer statement)'"></pre>
                                </div>
                                <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg space-y-1">
                                    <span class="font-bold text-gray-500 uppercase text-[10px] block">Normalized Output</span>
                                    <div class="bg-white p-2.5 rounded border text-xs font-mono font-bold text-emerald-700"
                                         x-text="JSON.stringify(selectedItem?.normalized_data?.correct_answers || [])"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Raw Text Traceability -->
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Source Segment Raw Text Block</h4>
                            <pre class="bg-gray-900 text-gray-100 p-4 rounded text-xs font-mono overflow-x-auto max-h-64 whitespace-pre-wrap"
                                 x-text="selectedItem?.raw_data?.debug_info?.raw_text_block || ''"></pre>
                        </div>
                    </div>

                    <!-- 3. ADMIN REVIEW MODE -->
                    <div x-show="modalMode === 'admin_review'" class="space-y-6">
                        <!-- Source & Confidence Breakdown Card -->
                        <template x-if="selectedItem && selectedItem.normalized_data?.source_reference">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div>
                                    <span class="text-gray-400 font-bold block uppercase text-[10px]">Source Range</span>
                                    <span class="font-semibold text-gray-800" x-text="'Pages ' + (selectedItem?.normalized_data?.source_reference?.page_start || 1) + '–' + (selectedItem?.normalized_data?.source_reference?.page_end || 1)"></span>
                                </div>
                                <div>
                                    <span class="text-gray-400 font-bold block uppercase text-[10px]">Type Classification</span>
                                    <span class="font-semibold text-navy" x-text="formatTypeName(selectedItem?.normalized_data?.question_type)"></span>
                                </div>
                                <div>
                                    <span class="text-gray-400 font-bold block uppercase text-[10px]">Overall Confidence</span>
                                    <span class="font-bold"
                                          :class="(selectedItem?.normalized_data?.source_reference?.confidence_score || 0) >= 85 ? 'text-emerald-600' : 'text-amber-600'"
                                          x-text="(selectedItem?.normalized_data?.source_reference?.confidence_score || 0) + '%'"></span>
                                </div>
                                <div>
                                    <span class="text-gray-400 font-bold block uppercase text-[10px]">Type Reason</span>
                                    <span class="text-gray-600 text-[10px]" x-text="selectedItem?.normalized_data?.source_reference?.type_detection_reason || 'Standard pattern'"></span>
                                </div>
                            </div>
                        </template>

                        <!-- Question Prompt -->
                        <div class="bg-gray-50 p-4 border border-gray-200 rounded-lg text-sm text-gray-900 font-medium whitespace-pre-line"
                             x-text="selectedItem?.normalized_data?.question_text || ''"></div>

                        <!-- Correct Options & Answers -->
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Options & Solutions</h4>
                            
                            <!-- Standard Options -->
                            <template x-if="selectedItem && selectedItem.normalized_data?.options && selectedItem.normalized_data.options.length">
                                <div class="space-y-2">
                                    <template x-for="opt in (selectedItem?.normalized_data?.options || [])" :key="opt.key">
                                        <div class="flex items-center space-x-3 p-3 border rounded-lg"
                                             :class="(selectedItem?.normalized_data?.correct_answers || []).includes(opt.key) ? 'border-emerald-500 bg-emerald-50/60 font-semibold' : 'border-gray-200 bg-white'">
                                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                                  :class="(selectedItem?.normalized_data?.correct_answers || []).includes(opt.key) ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-600'"
                                                  x-text="opt.key"></span>
                                            <span class="text-sm text-gray-800 flex-grow" x-text="opt.text"></span>
                                            <template x-if="(selectedItem?.normalized_data?.correct_answers || []).includes(opt.key)">
                                                <span class="text-xs font-bold text-emerald-600">✓ Correct</span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Dropdown Box Solutions -->
                            <template x-if="selectedItem && selectedItem.normalized_data?.answer_area?.boxes?.length">
                                <div class="space-y-2 p-3 bg-emerald-50/40 border border-emerald-200 rounded-lg">
                                    <span class="text-xs font-bold text-emerald-800 block">Structured Hotspot Answers:</span>
                                    <template x-for="box in (selectedItem?.normalized_data?.answer_area?.boxes || [])" :key="box.box_number || box.label">
                                        <div class="flex items-center space-x-2 text-xs">
                                            <span class="font-bold text-emerald-700" x-text="box.label + ':'"></span>
                                            <span class="text-gray-800 font-semibold" x-text="box.correct"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Drag Sequence Solutions -->
                            <template x-if="selectedItem && selectedItem.normalized_data?.answer_area?.steps?.length">
                                <div class="space-y-2 p-3 bg-emerald-50/40 border border-emerald-200 rounded-lg">
                                    <span class="text-xs font-bold text-emerald-800 block">Structured Drag & Drop Sequence:</span>
                                    <template x-for="step in (selectedItem?.normalized_data?.answer_area?.steps || [])" :key="step.step_number || step.label">
                                        <div class="flex items-center space-x-2 text-xs">
                                            <span class="font-bold text-emerald-700" x-text="step.label + ':'"></span>
                                            <span class="text-gray-800 font-semibold" x-text="step.text"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Answer Visual Exhibits -->
                        <template x-if="selectedItem && selectedItem.normalized_data?.answer_exhibits && selectedItem.normalized_data.answer_exhibits.length">
                            <div class="space-y-2 border-t pt-3">
                                <h4 class="text-xs font-bold text-amber-600 uppercase">Answer-Only Visual Solutions (Protected)</h4>
                                <div class="grid grid-cols-1 gap-3">
                                    <template x-for="img in (selectedItem?.normalized_data?.answer_exhibits || [])" :key="img.url">
                                        <div class="border border-amber-200 rounded p-2 bg-amber-50/40">
                                            <img :src="img.url" :alt="img.caption" class="max-h-80 mx-auto rounded shadow-sm">
                                            <span class="text-[10px] text-amber-700 block text-center mt-1">Highlighted Solution Exhibit</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Explanation -->
                        <div x-show="selectedItem && selectedItem.normalized_data?.explanation" class="space-y-1">
                            <h4 class="text-xs font-bold text-gray-400 uppercase">Explanation & Rationale</h4>
                            <div class="bg-gray-50 p-3 border border-gray-200 rounded text-xs text-gray-700 whitespace-pre-line"
                                 x-text="selectedItem?.normalized_data?.explanation || ''"></div>
                        </div>

                        <!-- References -->
                        <div x-show="selectedItem && selectedItem.normalized_data?.references?.length" class="space-y-1">
                            <h4 class="text-xs font-bold text-gray-400 uppercase">Reference Documentation Links</h4>
                            <ul class="list-disc pl-4 text-xs text-navy space-y-1">
                                <template x-for="ref in (selectedItem?.normalized_data?.references || [])" :key="ref.url">
                                    <li>
                                        <a :href="ref.url" target="_blank" class="text-cyan hover:underline font-medium" x-text="ref.url"></a>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <!-- 4. EDIT MODE -->
                    <div x-show="modalMode === 'edit'" class="space-y-6">
                        <!-- Top Metadata Row -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Target Exam <span class="text-rose-500">*</span></label>
                                <select x-model="editForm.exam_id" class="w-full border-gray-300 rounded text-xs p-2 focus:border-cyan focus:ring-cyan">
                                    <option value="">-- Select Target Exam --</option>
                                    @foreach($exams as $ex)
                                        <option value="{{ $ex->id }}">{{ $ex->exam_code }} — {{ $ex->exam_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Topic / Domain</label>
                                <input type="text" x-model="editForm.topic" class="w-full border-gray-300 rounded text-xs p-2 focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Question Type <span class="text-rose-500">*</span></label>
                                <select x-model="editForm.question_type" class="w-full border-gray-300 rounded text-xs p-2 focus:border-cyan focus:ring-cyan">
                                    <option value="single_choice">Single Choice</option>
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="hotspot">Hotspot / Dropdown</option>
                                    <option value="drag_drop">Drag & Drop</option>
                                    <option value="yes_no">Yes / No</option>
                                    <option value="case_study">Case Study</option>
                                    <option value="simulation">Simulation</option>
                                </select>
                            </div>
                        </div>

                        <!-- Question Prompt -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Question Prompt Text <span class="text-rose-500">*</span></label>
                            <textarea x-model="editForm.question_text" rows="4" class="w-full border-gray-300 rounded text-xs p-2.5 font-mono focus:border-cyan focus:ring-cyan"></textarea>
                        </div>

                        <!-- MCQ Options Section -->
                        <div x-show="editForm.question_type === 'single_choice' || editForm.question_type === 'multiple_choice' || editForm.question_type === 'yes_no'" class="space-y-3 border-t pt-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-gray-700 uppercase">Answer Options & Correct Answer(s)</h4>
                                <button type="button" @click="addEditOption()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded transition">
                                    + Add Option
                                </button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(opt, idx) in editForm.options" :key="idx">
                                    <div class="flex items-center space-x-3 p-2.5 border border-gray-200 rounded-lg bg-gray-50">
                                        <label class="flex items-center space-x-1.5 cursor-pointer">
                                            <input type="checkbox" :value="opt.key" :checked="editForm.correct_answers.includes(opt.key)" @change="toggleEditCorrectAnswer(opt.key)" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                                            <span class="font-bold text-xs w-6 text-center" x-text="opt.key"></span>
                                        </label>
                                        <input type="text" x-model="opt.text" placeholder="Option text..." class="flex-grow border-gray-300 rounded text-xs p-2 focus:border-cyan focus:ring-cyan">
                                        <button type="button" @click="removeEditOption(idx)" class="text-rose-500 hover:text-rose-700 text-xs font-bold px-2">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Hotspot Boxes Editor -->
                        <div x-show="editForm.question_type === 'hotspot'" class="space-y-3 border-t pt-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-gray-700 uppercase">Hotspot Dropdown Boxes</h4>
                                <button type="button" @click="addEditHotspotBox()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded transition">
                                    + Add Box
                                </button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(box, bIdx) in editForm.answer_area.boxes" :key="bIdx">
                                    <div class="p-3 border border-gray-200 rounded-lg bg-gray-50 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-xs text-gray-700" x-text="'Box #' + (bIdx + 1)"></span>
                                            <button type="button" @click="removeEditHotspotBox(bIdx)" class="text-rose-500 hover:text-rose-700 text-xs font-bold">Remove</button>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <input type="text" x-model="box.label" placeholder="Label (e.g. Box 1)" class="border-gray-300 rounded text-xs p-2">
                                            <input type="text" x-model="box.correct" placeholder="Correct Selection Text" class="border-gray-300 rounded text-xs p-2 font-bold text-emerald-700">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Drag & Drop Sequence Steps Editor -->
                        <div x-show="editForm.question_type === 'drag_drop'" class="space-y-3 border-t pt-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-gray-700 uppercase">Drag & Drop Sequence Steps</h4>
                                <button type="button" @click="addEditDragStep()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded transition">
                                    + Add Step
                                </button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(step, sIdx) in editForm.answer_area.steps" :key="sIdx">
                                    <div class="flex items-center space-x-2 p-2.5 border border-gray-200 rounded-lg bg-gray-50">
                                        <span class="font-bold text-cyan text-xs w-16" x-text="'Step ' + (sIdx + 1) + ':'"></span>
                                        <input type="text" x-model="step.text" placeholder="Action description..." class="flex-grow border-gray-300 rounded text-xs p-2">
                                        <button type="button" @click="removeEditDragStep(sIdx)" class="text-rose-500 hover:text-rose-700 text-xs font-bold px-2">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Explanation -->
                        <div class="border-t pt-4">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Explanation & Rationale</label>
                            <textarea x-model="editForm.explanation" rows="3" class="w-full border-gray-300 rounded text-xs p-2.5 focus:border-cyan focus:ring-cyan"></textarea>
                        </div>

                        <!-- References -->
                        <div class="border-t pt-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700">Documentation References</label>
                                <button type="button" @click="addEditReference()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded transition">
                                    + Add URL
                                </button>
                            </div>
                            <div class="space-y-2">
                                <template x-for="(ref, rIdx) in editForm.references" :key="rIdx">
                                    <div class="flex items-center space-x-2">
                                        <input type="url" x-model="ref.url" placeholder="https://..." class="flex-grow border-gray-300 rounded text-xs p-2 font-mono">
                                        <button type="button" @click="removeEditReference(rIdx)" class="text-rose-500 hover:text-rose-700 text-xs font-bold px-2">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t">
                            <a :href="'/admin/questions/import/item/' + (selectedItem ? selectedItem.id : '') + '/edit'" target="_blank" class="text-xs text-navy font-bold hover:underline flex items-center">
                                <span>Open in Full Dedicated Edit Page</span>
                                <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <div class="flex space-x-2">
                                <button type="button" @click="modalMode = 'admin_review'" class="px-4 py-2 border rounded text-xs font-bold text-gray-600 hover:bg-gray-50">Cancel</button>
                                <button type="button" @click="saveEdit()" :disabled="isSavingEdit" class="px-6 py-2 bg-navy text-white text-xs font-bold rounded shadow hover:bg-opacity-95 disabled:opacity-50">
                                    <span x-text="isSavingEdit ? 'Saving...' : 'Save Changes'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- IMPORT PROGRESS / SUCCESS MODAL -->
    <div x-show="isImporting || importSuccess" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-70 p-4" style="display: none;">
        <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-2xl text-center space-y-4">
            <template x-if="isImporting">
                <div class="space-y-3">
                    <h3 class="text-base font-bold text-gray-800">Importing Questions...</h3>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div class="bg-cyan h-3 rounded-full transition-all duration-300" :style="'width: ' + importProgress + '%'"></div>
                    </div>
                    <p class="text-xs text-gray-500">Writing normalized questions, options, and relations to the database...</p>
                </div>
            </template>

            <template x-if="importSuccess">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center mx-auto">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Import Successful!</h3>
                    <p class="text-xs text-gray-600">Successfully imported <strong x-text="importedCount"></strong> questions into the question bank as drafts.</p>
                    <div class="flex justify-center space-x-3 pt-2">
                        <a href="{{ route('admin.questions.index') }}" class="px-4 py-2 bg-navy text-white font-bold text-xs rounded shadow hover:bg-opacity-95 transition">
                            View Questions Listing
                        </a>
                        <a href="{{ route('admin.questions.import-history') }}" class="px-4 py-2 border border-gray-300 text-gray-700 font-bold text-xs rounded hover:bg-gray-50 transition">
                            View Import History
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function reviewBatchComponent() {
    return {
        items: @json($items),
        selectedIds: @json($items->where('normalized_data.readiness_status', 'READY')->pluck('id')->values()),
        activeFilter: 'all',
        topicFilter: '',
        typeFilter: '',
        searchQuery: '',
        selectedItem: null,
        modalMode: 'learner_preview',
        editForm: {
            id: null,
            exam_id: '',
            topic: '',
            question_type: 'single_choice',
            question_text: '',
            instructions: '',
            options: [],
            correct_answers: [],
            answer_area: {
                boxes: [],
                steps: []
            },
            explanation: '',
            references: []
        },
        isSavingEdit: false,
        isImporting: false,
        importProgress: 0,
        importSuccess: false,
        importedCount: 0,
        replaceDuplicates: false,

        get readyCount() {
            return this.items.filter(i => i.normalized_data.readiness_status === 'READY').length;
        },

        get reviewRequiredCount() {
            return this.items.filter(i => i.normalized_data.readiness_status === 'REVIEW_REQUIRED').length;
        },

        get failedCount() {
            return this.items.filter(i => i.normalized_data.readiness_status === 'FAILED').length;
        },

        get availableTopics() {
            const topics = new Set();
            this.items.forEach(i => {
                if (i.normalized_data.topic) topics.add(i.normalized_data.topic);
            });
            return Array.from(topics);
        },

        get filteredItems() {
            return this.items.filter(item => {
                const readiness = item.normalized_data.readiness_status;
                if (this.activeFilter === 'ready' && readiness !== 'READY') return false;
                if (this.activeFilter === 'review_required' && readiness !== 'REVIEW_REQUIRED') return false;
                if (this.activeFilter === 'failed' && readiness !== 'FAILED') return false;
                if (this.activeFilter === 'duplicate' && item.validation_status !== 'duplicate') return false;

                if (this.topicFilter && item.normalized_data.topic !== this.topicFilter) return false;
                if (this.typeFilter && item.normalized_data.question_type !== this.typeFilter) return false;

                if (this.searchQuery.trim()) {
                    const q = this.searchQuery.toLowerCase();
                    const text = (item.normalized_data.question_text || '').toLowerCase();
                    const topic = (item.normalized_data.topic || '').toLowerCase();
                    if (!text.includes(q) && !topic.includes(q)) return false;
                }

                return true;
            });
        },

        formatTypeName(type) {
            const map = {
                'single_choice': 'Single Choice',
                'multiple_choice': 'Multiple Choice',
                'hotspot': 'Hotspot / Dropdown',
                'drag_drop': 'Drag & Drop',
                'yes_no': 'Yes / No',
                'case_study': 'Case Study',
                'simulation': 'Simulation',
                'unknown': 'Unknown Type'
            };
            return map[type] || type || 'Single Choice';
        },

        selectReadyOnly() {
            const readyItems = this.items.filter(i => i.normalized_data.readiness_status === 'READY');
            this.selectedIds = readyItems.map(i => i.id);
        },

        selectAllFiltered() {
            const filtered = this.filteredItems.filter(i => i.normalized_data.readiness_status !== 'FAILED');
            const allSelected = filtered.every(i => this.selectedIds.includes(i.id));
            if (allSelected) {
                const filteredIds = filtered.map(i => i.id);
                this.selectedIds = this.selectedIds.filter(id => !filteredIds.includes(id));
            } else {
                filtered.forEach(i => {
                    if (!this.selectedIds.includes(i.id)) this.selectedIds.push(i.id);
                });
            }
        },

        openReview(item, mode = 'learner_preview') {
            this.selectedItem = item;
            this.modalMode = mode;
            const data = item.normalized_data || {};
            this.editForm = {
                id: item.id,
                exam_id: data.exam_id || '{{ $batch->default_exam_id ?? '' }}',
                topic: data.topic || 'Topic 1',
                question_type: data.question_type || 'single_choice',
                question_text: data.question_text || '',
                instructions: data.instructions || '',
                options: JSON.parse(JSON.stringify(data.options || [])),
                correct_answers: JSON.parse(JSON.stringify(data.correct_answers || [])),
                answer_area: {
                    boxes: JSON.parse(JSON.stringify(data.answer_area?.boxes || [])),
                    steps: JSON.parse(JSON.stringify(data.answer_area?.steps || []))
                },
                explanation: data.explanation || '',
                references: JSON.parse(JSON.stringify(data.references || []))
            };
        },

        addEditOption() {
            const nextKey = String.fromCharCode(65 + this.editForm.options.length);
            this.editForm.options.push({ key: nextKey, text: '' });
        },

        removeEditOption(index) {
            const removed = this.editForm.options[index];
            this.editForm.options.splice(index, 1);
            this.editForm.correct_answers = this.editForm.correct_answers.filter(k => k !== removed.key);
            this.editForm.options.forEach((opt, idx) => {
                const newKey = String.fromCharCode(65 + idx);
                const oldKey = opt.key;
                opt.key = newKey;
                const caIdx = this.editForm.correct_answers.indexOf(oldKey);
                if (caIdx !== -1) {
                    this.editForm.correct_answers[caIdx] = newKey;
                }
            });
        },

        toggleEditCorrectAnswer(key) {
            if (this.editForm.question_type === 'single_choice' || this.editForm.question_type === 'yes_no') {
                this.editForm.correct_answers = [key];
            } else {
                if (this.editForm.correct_answers.includes(key)) {
                    this.editForm.correct_answers = this.editForm.correct_answers.filter(k => k !== key);
                } else {
                    this.editForm.correct_answers.push(key);
                }
            }
        },

        addEditHotspotBox() {
            const boxNum = this.editForm.answer_area.boxes.length + 1;
            this.editForm.answer_area.boxes.push({
                box_number: boxNum,
                label: 'Box ' + boxNum,
                correct: ''
            });
        },

        removeEditHotspotBox(index) {
            this.editForm.answer_area.boxes.splice(index, 1);
        },

        addEditDragStep() {
            const stepNum = this.editForm.answer_area.steps.length + 1;
            this.editForm.answer_area.steps.push({
                step_number: stepNum,
                label: 'Step ' + stepNum,
                text: ''
            });
        },

        removeEditDragStep(index) {
            this.editForm.answer_area.steps.splice(index, 1);
        },

        addEditReference() {
            this.editForm.references.push({ title: 'Documentation', url: '' });
        },

        removeEditReference(index) {
            this.editForm.references.splice(index, 1);
        },

        async saveEdit() {
            this.isSavingEdit = true;
            try {
                const res = await fetch(`/admin/questions/import/item/${this.editForm.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.editForm)
                });
                const data = await res.json();
                if (data.success) {
                    const idx = this.items.findIndex(i => i.id === data.item.id);
                    if (idx !== -1) {
                        this.items[idx] = data.item;
                        this.selectedItem = data.item;
                    }
                    this.modalMode = 'admin_review';
                } else {
                    alert(data.message || 'Failed to update question.');
                }
            } catch (err) {
                alert('Error saving question: ' + err.message);
            } finally {
                this.isSavingEdit = false;
            }
        },

        async startImport() {
            if (this.selectedIds.length === 0) {
                alert('Please select at least one question to import.');
                return;
            }

            if (!confirm(`Import ${this.selectedIds.length} questions into the live question bank?`)) {
                return;
            }

            this.isImporting = true;
            this.importProgress = 10;

            try {
                const res = await fetch(`/admin/questions/import/batch/{{ $batch->uuid }}/confirm`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        item_ids: this.selectedIds,
                        replace_duplicates: this.replaceDuplicates
                    })
                });

                this.importProgress = 100;
                const data = await res.json();

                if (data.success) {
                    this.importedCount = data.imported_count;
                    this.importSuccess = true;
                } else {
                    alert(data.message || 'Import error.');
                }
            } catch (err) {
                alert('Import request failed: ' + err.message);
            } finally {
                this.isImporting = false;
            }
        }
    };
}
</script>
@endsection
