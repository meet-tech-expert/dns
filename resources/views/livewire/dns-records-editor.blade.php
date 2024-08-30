<div>
    @if($loading)
        <div class="text-center">
            <p class="text-lg font-semibold text-gray-600">{{ __('Loading DNS records...') }}</p>
        </div>
    @else
        <!-- Add/Edit Record Section -->
        @if($showEditForm || $showAddForm)
            <div class="mb-6">
                <h5 class="text-xl font-semibold mb-3 text-gray-700">{{ $showEditForm ? __('Edit DNS Record') : __('Add DNS Record') }}</h5>
                <div class="table-responsive">
                    <table class="table border-0 align-items-center">
                        <thead class="border-bottom">
                            <tr>
                                <th class="text-sm font-semibold text-gray-600">{{ __('Record Name') }}</th>
                                <th class="text-sm font-semibold text-gray-600">{{ __('Content') }}</th>
                                @if(in_array($showEditForm ? $editingRecord['type'] : $newRecord['type'], ['MX', 'SRV']))
                                    <th class="text-sm font-semibold text-gray-600">{{ __('Priority') }}</th>
                                @endif
                                <th class="text-sm font-semibold text-gray-600">{{ __('TTL') }}</th>
                                <th class="text-sm font-semibold text-gray-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="text" wire:model.defer="{{ $showEditForm ? 'editingRecord.name' : 'newRecord.name' }}" 
                                           class="form-control border border-gray-300 px-3 py-2" 
                                           placeholder="{{ __('e.g., example.com') }}">
                                </td>
                                <td>
                                    <input type="text" wire:model.defer="{{ $showEditForm ? 'editingRecord.content' : 'newRecord.content' }}" 
                                           class="form-control border border-gray-300 px-3 py-2" 
                                           placeholder="{{ __('e.g., 192.168.0.1') }}">
                                </td>
                                @if(in_array($showEditForm ? $editingRecord['type'] : $newRecord['type'], ['MX', 'SRV']))
                                    <td>
                                        <input type="number" wire:model.defer="{{ $showEditForm ? 'editingRecord.priority' : 'newRecord.priority' }}" 
                                            class="form-control border border-gray-300 px-3 py-2" 
                                            placeholder="{{ __('Priority') }}">
                                    </td>
                                @endif
                                <td>
                                    <input type="number" wire:model.defer="{{ $showEditForm ? 'editingRecord.ttl' : 'newRecord.ttl' }}" 
                                           class="form-control border border-gray-300 px-3 py-2" 
                                           placeholder="{{ __('e.g., 3600 (seconds, 1 = Auto)') }}">
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center">
                                        <button wire:click="{{ $showEditForm ? 'updateRecord' : 'addRecord' }}" wire:loading.attr="disabled" wire:target="{{ $showEditForm ? 'updateRecord' : 'addRecord' }}" class="btn btn-primary px-4 py-2">
                                            <span wire:loading wire:target="{{ $showEditForm ? 'updateRecord' : 'addRecord' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise fa-spin" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                                                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                                                    </svg>
                                            </span>
                                            <span wire:loading.remove wire:target="{{ $showEditForm ? 'updateRecord' : 'addRecord' }}">
                                                {{ $showEditForm ? __('Update Record') : __('Add Record') }}
                                            </span>
                                        </button>
                                        <button wire:click="$set('{{ $showEditForm ? 'showEditForm' : 'showAddForm' }}', false)" 
                                                class="btn btn-secondary ml-2 px-4 py-2">
                                            {{ __('Cancel') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="d-flex justify-content-start align-items-center mb-3">
            <div class="dropdown me-3">
                <button class="btn btn-secondary dropdown-toggle hide-after" wire:target="importFile, triggerImport, importRecords" wire:loading.attr="disabled" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span wire:loading.remove wire:target="exportRecords">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                          </svg> {{ __('Export') }}
                    </span>
                    <span wire:loading wire:target="exportRecords">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise fa-spin" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                          </svg>
                    </span>
                </button>
                <ul class="dropdown-menu hide-before" aria-labelledby="exportDropdown">
                    <li><a wire:click="exportRecords('csv')" class="dropdown-item">{{ __('Export as CSV') }}</a></li>
                    <li><a wire:click="exportRecords('json')" class="dropdown-item">{{ __('Export as JSON') }}</a></li>
                </ul>
            </div>
        
            <div>
                <input type="file" wire:model="importFile" id="importFileInput" class="form-control" accept=".csv" style="display: none; width: auto; height: 38px;">
                <button wire:click="triggerImport" wire:loading.attr="disabled" wire:target="importFile, triggerImport, importRecords" class="btn btn-primary">
                    <span wire:loading.remove wire:target="importFile, importRecords"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-arrow-up" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M7.646 5.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708z"/>
                        <path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383m.653.757c-.757.653-1.153 1.44-1.153 2.056v.448l-.445.049C2.064 6.805 1 7.952 1 9.318 1 10.785 2.23 12 3.781 12h8.906C13.98 12 15 10.988 15 9.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 4.825 10.328 3 8 3a4.53 4.53 0 0 0-2.941 1.1z"/>
                      </svg> {{ __('Import') }}</span>
                    <span wire:loading wire:target="importFile, importRecords">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise fa-spin" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                          </svg>
                    </span>
                </button>
            </div>
        
            <div class="ms-auto d-flex justify-content-end align-items-center">
                <select wire:model.defer="{{ $showEditForm ? 'editingRecord.type' : 'newRecord.type' }}"  class="form-control border border-secondary me-3" style="width: auto; height: 38px;">
                    <option value="A" selected>A</option>
                    <option value="AAAA">AAAA</option>
                    <option value="CNAME">CNAME</option>
                    <option value="TXT">TXT</option>
                    <option value="MX">MX</option>
                    <option value="NS">NS</option>
                    <option value="SRV">SRV</option>
                </select>
                <button wire:click="$set('showAddForm', true)" class="btn btn-success d-flex align-items-center" style="height: 38px; margin-bottom: 0px;">
                    <svg style="margin-right: 1em;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                      </svg> {{ __('Add New Record') }}
                </button>
            </div>
        </div>
        

        <!-- DNS Records Table -->
        <div class="table-responsive">
            <table class="table align-items-center">
                <thead>
                    <tr class="text-uppercase text-secondary text-sm font-semibold">
                        <th>{{ __('Record Name') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Content') }}</th>
                        <th>{{ __('TTL') }}</th>
                        <th>{{ __('Proxied') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr class="border-bottom">
                            <td class="font-semibold text-gray-700">{{ $record->name }} @if(in_array($record->type, ['MX', 'SRV']))
                                <span class="font-bold text-gray-700">(P: {{ $record->priority ?? 'N/A' }})</span>
                            @endif</td>
                            <td class="text-gray-600">{{ $record->type }}</td>
                            <td class="text-gray-600">{{ $record->content }}</td>
                            <td class="text-gray-600">
                                @if($record->ttl == 1)
                                    Auto
                                @elseif($record->ttl < 60)
                                    {{ $record->ttl }} sec
                                @elseif($record->ttl < 3600)
                                    {{ round($record->ttl / 60) }} min
                                @elseif($record->ttl < 86400)
                                    {{ round($record->ttl / 3600) }} hr
                                @else
                                    {{ round($record->ttl / 86400) }} days
                                @endif
                            </td>                            
                            <td>
                                <button wire:click="toggleProxy('{{ $record->id }}')" wire:loading.attr="disabled" class="btn btn-sm">
                                    <span wire:loading wire:target="toggleProxy('{{ $record->id }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise fa-spin" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                                          </svg>
                                    </span>
                                    <span wire:loading.remove wire:target="toggleProxy('{{ $record->id }}')">
                                        @if($record->proxied)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" style="color: #F48120; font-weight: bold;" class="bi bi-shield-check" viewBox="0 0 16 16">
                                            <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                                            <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                        </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-x" viewBox="0 0 16 16">
                                                <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                                                <path d="M6.146 5.146a.5.5 0 0 1 .708 0L8 6.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 7l1.147 1.146a.5.5 0 0 1-.708.708L8 7.707 6.854 8.854a.5.5 0 1 1-.708-.708L7.293 7 6.146 5.854a.5.5 0 0 1 0-.708"/>
                                                </svg>
                                        @endif
                                    </span>
                                </button>                                
                            </td>
                            <td class="text-right">
                                <button wire:click="deleteRecord('{{ $record->id }}', false)" wire:loading.attr="disabled" class="btn btn-sm btn-danger mx-2">
                                    <span wire:loading wire:target="deleteRecord('{{ $record->id }}', false)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise fa-spin" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                                          </svg>
                                    </span>
                                    <span wire:loading.remove wire:target="deleteRecord('{{ $record->id }}', false)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                            </svg> {{ __('Delete') }}
                                    </span>
                                </button>
                                <button wire:click="deleteRecord('{{ $record->id }}', true)" wire:loading.attr="disabled" class="btn btn-sm btn-warning mx-2">
                                    <span wire:loading wire:target="deleteRecord('{{ $record->id }}', true)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise fa-spin" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                                          </svg>
                                    </span>
                                    <span wire:loading.remove wire:target="deleteRecord('{{ $record->id }}', true)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                            </svg> {{ __('Soft Delete') }}
                                    </span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-600">{{ __('No DNS records found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('triggerFileInput', () => {
                document.getElementById('importFileInput').click();
            });
        });
    </script>
    
</div>
