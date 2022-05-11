<x-layout>
    <x-slot name="app_care_portal"></x-slot>
    <x-slot name="appheader">
        <app-header
            v-model="filteredData"
            :filter-data="{{ $persons }}"
            :filter-type="['search']"
        >Clients page</app-header>
    </x-slot>
    <div class="wrap">
        <empty-state v-if="!Object.keys(filteredData).length" :asset-url="'{{$assetUrl}}'">
            No clients
        </empty-state>
        <person-card
            v-for="person in filteredData"
            :key="person.Id"
            :person="person"
            type="client"
        ></person-card>
    </div>
</x-layout>
<script>
    document.addEventListener("DOMContentLoaded", function (event) {
        new Vue({
            delimiters: ['${', '}'],
            el: '#app',
            data() {
                return {
                    message: {},
                    persons: {},
                    filteredData: {}
                }
            },
            mounted() {
                window.jQuery('#app').prop('style', '');
                window.jQuery('.addCG').hide();
            },
            computed: {
                filteredDataWithRemoved() {
                    const result = {}
                    for (const index in this.filteredData) {
                        if (!this.removedPersons[this.filteredData[index].Id]) {
                            result[index] = this.filteredData[index]
                        }
                    }
                    return result;
                }
            },
            methods: {}

        });
    });
</script>
