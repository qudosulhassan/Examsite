{{-- Free sample questions + unlock CTA for an exam page. Needs $exam and $sampleQuestions. --}}
                <!-- Sample Questions -->
                <div class="space-y-10 pt-8">
                    <div class="text-center mb-12">
                        <span class="text-[11px] font-black text-cyan uppercase tracking-widest bg-cyan/10 px-3 py-1.5 rounded-full border border-cyan/20 mb-4 inline-block">Free Trial</span>
                        <h2 class="text-3xl sm:text-4xl font-black text-navy mb-4 tracking-tight">Actual Free {{ $exam->vendor->name }} {{ $exam->exam_code }} Practice Questions</h2>
                    </div>
                    
                    @foreach($sampleQuestions as $index => $question)
                        <div class="bg-white border border-gray-100 rounded-[32px] p-8 sm:p-10 text-navy space-y-8 shadow-[0_15px_40px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_50px_rgba(0,212,170,0.08)] hover:border-cyan/30 transition-all duration-300 relative overflow-hidden" x-data="{ selectedOption: null, checked: false }">
                            
                            <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-cyan to-blue-500 opacity-50"></div>

                            <!-- Question Header -->
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pb-6 border-b border-gray-100">
                                <div class="flex items-center space-x-4">
                                    <span class="bg-navy text-white text-sm font-black px-4 py-1.5 rounded-lg tracking-widest uppercase shadow-md">Question {{ $index + 1 }}</span>
                                </div>
                                <span class="bg-gray-50 text-gray-500 text-[10px] font-black px-4 py-2 rounded-xl border border-gray-100 uppercase tracking-widest truncate max-w-full sm:max-w-xs">{{ $question->topic }}</span>
                            </div>
                            
                            <!-- Question Text -->
                            <p class="text-base sm:text-lg font-normal leading-relaxed text-gray-800">{!! $question->question_text !!}</p>
                            
                            <!-- Options list styled -->
                            <div class="space-y-4">
                                <!-- Option A -->
                                <button x-on:click="if(!checked) selectedOption = 'A'"
                                        class="w-full text-left p-5 rounded-2xl border-2 text-base transition-all flex items-center justify-between group/opt shadow-sm"
                                        :class="[
                                            !checked && selectedOption === 'A' ? 'border-cyan bg-cyan/5 text-navy font-bold shadow-[0_0_20px_rgba(0,212,170,0.15)] scale-[1.01]' : '',
                                            !checked && selectedOption !== 'A' ? 'border-gray-100 hover:border-gray-300 hover:bg-gray-50 text-gray-700 font-normal' : '',
                                            checked && 'A' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-50 text-green-800 font-bold shadow-md' : '',
                                            checked && selectedOption === 'A' && 'A' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-50 text-red-800 font-bold' : '',
                                            checked && 'A' !== '{{ $question->correct_option }}' && selectedOption !== 'A' ? 'border-gray-50 opacity-40 text-gray-400 cursor-not-allowed font-normal' : ''
                                        ]"
                                        :disabled="checked">
                                    <div class="flex items-center space-x-5 pr-4">
                                        <!-- Custom Radio -->
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors shadow-sm"
                                             :class="[
                                                selectedOption === 'A' && !checked ? 'border-cyan' : '',
                                                selectedOption !== 'A' && !checked ? 'border-gray-300 group-hover/opt:border-gray-400' : '',
                                                checked && 'A' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500' : '',
                                                checked && selectedOption === 'A' && 'A' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500' : '',
                                                checked && 'A' !== '{{ $question->correct_option }}' && selectedOption !== 'A' ? 'border-gray-300' : ''
                                             ]">
                                            <div class="w-3 h-3 rounded-full transition-all"
                                                 :class="[
                                                    selectedOption === 'A' && !checked ? 'bg-cyan scale-100' : 'scale-0',
                                                    checked && 'A' === '{{ $question->correct_option }}' ? 'bg-white scale-100' : '',
                                                    checked && selectedOption === 'A' && 'A' !== '{{ $question->correct_option }}' ? 'bg-white scale-100' : ''
                                                 ]"></div>
                                        </div>
                                        <span class="leading-relaxed font-normal text-gray-700"><span class="mr-2 font-bold text-navy text-base">A.</span> {{ $question->option_a }}</span>
                                    </div>
                                    
                                    <!-- Status Icons -->
                                    <template x-if="checked && 'A' === '{{ $question->correct_option }}'">
                                        <svg class="h-8 w-8 text-green-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    </template>
                                    <template x-if="checked && selectedOption === 'A' && 'A' !== '{{ $question->correct_option }}'">
                                        <svg class="h-8 w-8 text-red-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </template>
                                </button>

                                <!-- Option B -->
                                <button x-on:click="if(!checked) selectedOption = 'B'"
                                        class="w-full text-left p-5 rounded-2xl border-2 text-base transition-all flex items-center justify-between group/opt shadow-sm"
                                        :class="[
                                            !checked && selectedOption === 'B' ? 'border-cyan bg-cyan/5 text-navy font-bold shadow-[0_0_20px_rgba(0,212,170,0.15)] scale-[1.01]' : '',
                                            !checked && selectedOption !== 'B' ? 'border-gray-100 hover:border-gray-300 hover:bg-gray-50 text-gray-700 font-normal' : '',
                                            checked && 'B' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-50 text-green-800 font-bold shadow-md' : '',
                                            checked && selectedOption === 'B' && 'B' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-50 text-red-800 font-bold' : '',
                                            checked && 'B' !== '{{ $question->correct_option }}' && selectedOption !== 'B' ? 'border-gray-50 opacity-40 text-gray-400 cursor-not-allowed font-normal' : ''
                                        ]"
                                        :disabled="checked">
                                    <div class="flex items-center space-x-5 pr-4">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors shadow-sm"
                                             :class="[
                                                selectedOption === 'B' && !checked ? 'border-cyan' : '',
                                                selectedOption !== 'B' && !checked ? 'border-gray-300 group-hover/opt:border-gray-400' : '',
                                                checked && 'B' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500' : '',
                                                checked && selectedOption === 'B' && 'B' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500' : '',
                                                checked && 'B' !== '{{ $question->correct_option }}' && selectedOption !== 'B' ? 'border-gray-300' : ''
                                             ]">
                                            <div class="w-3 h-3 rounded-full transition-all"
                                                 :class="[
                                                    selectedOption === 'B' && !checked ? 'bg-cyan scale-100' : 'scale-0',
                                                    checked && 'B' === '{{ $question->correct_option }}' ? 'bg-white scale-100' : '',
                                                    checked && selectedOption === 'B' && 'B' !== '{{ $question->correct_option }}' ? 'bg-white scale-100' : ''
                                                 ]"></div>
                                        </div>
                                        <span class="leading-relaxed font-normal text-gray-700"><span class="mr-2 font-bold text-navy text-base">B.</span> {{ $question->option_b }}</span>
                                    </div>
                                    <template x-if="checked && 'B' === '{{ $question->correct_option }}'">
                                        <svg class="h-8 w-8 text-green-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    </template>
                                    <template x-if="checked && selectedOption === 'B' && 'B' !== '{{ $question->correct_option }}'">
                                        <svg class="h-8 w-8 text-red-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </template>
                                </button>

                                <!-- Option C -->
                                @if(!empty($question->option_c))
                                    <button x-on:click="if(!checked) selectedOption = 'C'"
                                            class="w-full text-left p-5 rounded-2xl border-2 text-base transition-all flex items-center justify-between group/opt shadow-sm"
                                            :class="[
                                                !checked && selectedOption === 'C' ? 'border-cyan bg-cyan/5 text-navy font-bold shadow-[0_0_20px_rgba(0,212,170,0.15)] scale-[1.01]' : '',
                                                !checked && selectedOption !== 'C' ? 'border-gray-100 hover:border-gray-300 hover:bg-gray-50 text-gray-700 font-normal' : '',
                                                checked && 'C' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-50 text-green-800 font-bold shadow-md' : '',
                                                checked && selectedOption === 'C' && 'C' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-50 text-red-800 font-bold' : '',
                                                checked && 'C' !== '{{ $question->correct_option }}' && selectedOption !== 'C' ? 'border-gray-50 opacity-40 text-gray-400 cursor-not-allowed font-normal' : ''
                                            ]"
                                            :disabled="checked">
                                        <div class="flex items-center space-x-5 pr-4">
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors shadow-sm"
                                                 :class="[
                                                    selectedOption === 'C' && !checked ? 'border-cyan' : '',
                                                    selectedOption !== 'C' && !checked ? 'border-gray-300 group-hover/opt:border-gray-400' : '',
                                                    checked && 'C' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500' : '',
                                                    checked && selectedOption === 'C' && 'C' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500' : '',
                                                    checked && 'C' !== '{{ $question->correct_option }}' && selectedOption !== 'C' ? 'border-gray-300' : ''
                                                 ]">
                                                <div class="w-3 h-3 rounded-full transition-all"
                                                     :class="[
                                                        selectedOption === 'C' && !checked ? 'bg-cyan scale-100' : 'scale-0',
                                                        checked && 'C' === '{{ $question->correct_option }}' ? 'bg-white scale-100' : '',
                                                        checked && selectedOption === 'C' && 'C' !== '{{ $question->correct_option }}' ? 'bg-white scale-100' : ''
                                                     ]"></div>
                                            </div>
                                            <span class="leading-relaxed font-normal text-gray-700"><span class="mr-2 font-bold text-navy text-base">C.</span> {{ $question->option_c }}</span>
                                        </div>
                                        <template x-if="checked && 'C' === '{{ $question->correct_option }}'">
                                            <svg class="h-8 w-8 text-green-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </template>
                                        <template x-if="checked && selectedOption === 'C' && 'C' !== '{{ $question->correct_option }}'">
                                            <svg class="h-8 w-8 text-red-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </template>
                                    </button>
                                @endif

                                <!-- Option D -->
                                @if(!empty($question->option_d))
                                    <button x-on:click="if(!checked) selectedOption = 'D'"
                                            class="w-full text-left p-5 rounded-2xl border-2 text-base transition-all flex items-center justify-between group/opt shadow-sm"
                                            :class="[
                                                !checked && selectedOption === 'D' ? 'border-cyan bg-cyan/5 text-navy font-bold shadow-[0_0_20px_rgba(0,212,170,0.15)] scale-[1.01]' : '',
                                                !checked && selectedOption !== 'D' ? 'border-gray-100 hover:border-gray-300 hover:bg-gray-50 text-gray-700 font-normal' : '',
                                                checked && 'D' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-50 text-green-800 font-bold shadow-md' : '',
                                                checked && selectedOption === 'D' && 'D' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-50 text-red-800 font-bold' : '',
                                                checked && 'D' !== '{{ $question->correct_option }}' && selectedOption !== 'D' ? 'border-gray-50 opacity-40 text-gray-400 cursor-not-allowed font-normal' : ''
                                            ]"
                                            :disabled="checked">
                                        <div class="flex items-center space-x-5 pr-4">
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors shadow-sm"
                                                 :class="[
                                                    selectedOption === 'D' && !checked ? 'border-cyan' : '',
                                                    selectedOption !== 'D' && !checked ? 'border-gray-300 group-hover/opt:border-gray-400' : '',
                                                    checked && 'D' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500' : '',
                                                    checked && selectedOption === 'D' && 'D' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500' : '',
                                                    checked && 'D' !== '{{ $question->correct_option }}' && selectedOption !== 'D' ? 'border-gray-300' : ''
                                                 ]">
                                                <div class="w-3 h-3 rounded-full transition-all"
                                                     :class="[
                                                        selectedOption === 'D' && !checked ? 'bg-cyan scale-100' : 'scale-0',
                                                        checked && 'D' === '{{ $question->correct_option }}' ? 'bg-white scale-100' : '',
                                                        checked && selectedOption === 'D' && 'D' !== '{{ $question->correct_option }}' ? 'bg-white scale-100' : ''
                                                     ]"></div>
                                            </div>
                                            <span class="leading-relaxed font-normal text-gray-700"><span class="mr-2 font-bold text-navy text-base">D.</span> {{ $question->option_d }}</span>
                                        </div>
                                        <template x-if="checked && 'D' === '{{ $question->correct_option }}'">
                                            <svg class="h-8 w-8 text-green-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </template>
                                        <template x-if="checked && selectedOption === 'D' && 'D' !== '{{ $question->correct_option }}'">
                                            <svg class="h-8 w-8 text-red-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </template>
                                    </button>
                                @endif
                            </div>

                            <!-- Grade actions -->
                            <div class="flex justify-between items-center pt-8 mt-6 border-t border-gray-100">
                                <span class="text-[11px] font-black text-gray-400 uppercase tracking-widest flex items-center bg-gray-50 px-3 py-1.5 rounded-md">
                                    <svg class="w-4 h-4 mr-2 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Select an option to check
                                </span>
                                <template x-if="!checked">
                                    <button x-on:click="checked = true"
                                            class="bg-navy text-white text-sm font-black px-8 py-3.5 rounded-xl shadow-[0_10px_20px_rgba(10,22,40,0.15)] hover:shadow-[0_15px_30px_rgba(10,22,40,0.2)] hover:-translate-y-1 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
                                            :disabled="!selectedOption">
                                        Check Answer
                                    </button>
                                </template>
                                <template x-if="checked">
                                    <span class="text-sm font-black px-6 py-3 rounded-xl shadow-sm" :class="selectedOption === '{{ $question->correct_option }}' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'">
                                        <span x-text="selectedOption === '{{ $question->correct_option }}' ? '✓ Correct Answer!' : '✗ Incorrect Answer'"></span>
                                    </span>
                                </template>
                            </div>

                            <!-- Explanation slide down -->
                            @if(!empty($question->explanation))
                            <div x-show="checked"
                                 x-transition:enter="transition ease-out duration-500"
                                 x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
                                 x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                                 class="mt-8 bg-gradient-to-r from-gray-50 to-white border-l-4 border-cyan p-6 sm:p-8 rounded-r-2xl shadow-sm space-y-3 relative overflow-hidden">

                                <div class="absolute -right-8 -top-8 w-24 h-24 bg-cyan/10 rounded-full blur-xl pointer-events-none"></div>

                                <div class="font-black text-navy text-[11px] uppercase tracking-widest flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                                    Expert Explanation
                                </div>
                                <div class="text-gray-600 leading-relaxed text-base font-normal relative z-10 space-y-3">{!! $question->explanation !!}</div>
                            </div>
                            @endif
                        </div>
                    @endforeach

                    <!-- CTA to buy full exam to unlock answers -->
                    <div class="relative bg-gradient-to-br from-orange to-red-600 rounded-[32px] p-10 sm:p-14 text-center space-y-8 shadow-[0_20px_50px_rgba(255,107,53,0.3)] overflow-hidden mt-12 group hover:scale-[1.01] transition-transform duration-500">
                        <!-- Abstract glowing backgrounds -->
                        <div class="absolute -right-20 -top-20 bg-white/20 w-80 h-80 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000 pointer-events-none"></div>
                        <div class="absolute -left-20 -bottom-20 bg-yellow-400/20 w-60 h-60 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000 pointer-events-none"></div>
                        
                        <h3 class="text-3xl sm:text-4xl font-black text-white relative z-10 leading-tight">Ready to master all {{ $exam->question_count }} questions?</h3>
                        <p class="text-lg text-white/90 font-normal max-w-2xl mx-auto relative z-10 leading-relaxed">Unlock full access to the timed Test Engine and downloadable PDF study guides. Practice under real exam conditions.</p>
                        
                        <a href="#purchase-card" class="inline-block bg-white text-orange hover:bg-gray-50 hover:text-red-500 text-lg font-black py-5 px-12 rounded-2xl shadow-[0_10px_30px_rgba(0,0,0,0.15)] transition-all transform hover:-translate-y-1 relative z-10 uppercase tracking-widest hover:shadow-[0_15px_40px_rgba(0,0,0,0.2)]">
                            Unlock Full Access Now
                        </a>
                    </div>
                </div>

