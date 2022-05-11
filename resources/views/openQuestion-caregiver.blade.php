<x-layout>
    <x-slot name="app_care_portal"></x-slot>
    <x-slot name="appheader">
        <app-header
            v-model="filteredData"
            :filter-data="{{ $OpenQuestions }}"
            filter-object="client"
        >Open Questions</app-header>
    </x-slot>
    <div class="wrap">
        <alert :message="message"></alert>
    </div>
    <div class="wrap">
        @if($OpenQuestions->isEmpty())
            <empty-state :asset-url="'{{$assetUrl}}'">
                No open questions
            </empty-state>
        @else
            <open-question
                v-for="question in filteredDataWithRemoved"
                :key="question.Id"
                section="caregiver"
                :care-givers="{{ $Caregivers }}"
                :client="question.client"
                :question="question"
                :date="question.DateTimeFrom"
                v-on:updated-data="updatedDataId"
            ></open-question>
        @endif
    </div>
</x-layout>
<script src="https://unpkg.com/element-ui/lib/index.js"></script>
<script>
    const message = JSON.parse('{!! $message !!}');
</script>
<script>
    document.addEventListener("DOMContentLoaded", function (event) {
        new Vue({
            delimiters: ['${', '}'],
            el: '#app',
            data() {
                return {
                    data: {},
                    updatedData: {},
                    loading: true,
                    message: {},
                    filteredData: {},
                }
            },
            mounted() {
                window.jQuery('#app').prop('style', '');
                window.jQuery('.addCG').hide();
                if (Object.keys(message).length > 0) {
                    this.message = message
                }
                window.addEventListener("beforeunload", () => {
                    axios.post('/event/ajaxPostHandler', {
                        action: 'clearSession'
                    })
                })
            },
            computed: {
                filteredDataWithRemoved() {
                    const result = {}
                    for (const index in this.filteredData) {
                        if (!this.updatedData[this.filteredData[index].Id]) {
                            result[index] = this.filteredData[index]
                        }
                    }
                    return result;
                }
            },
            methods: {
                changedTeam(teamId) {
                    axios.get('/myTeams', {
                        params: {
                            action: 'showMyTeam',
                            teamId: teamId
                        }
                    })
                        .then((response) => {

                        })
                },
                updatedDataId(value) {
                    if (value.updated) {
                        this.updatedData[value.id] = true;
                        this.updatedData = {...this.updatedData}
                        this.message = {
                            text: "The question is scheduled.",
                            type: "success"
                        }
                    } else {
                        alert('Some error occured!')
                    }
                }
            }

        });
    });
</script>

