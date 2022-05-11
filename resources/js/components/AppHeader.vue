<template>
    <header class="headline with-actions search-only">
        <div class="wrap">
            <div class="actions">
                <div v-if="filterType.indexOf('search')>-1" :class="`input-group search ${filterType.length === 2?'split-60':''}`">
                    <input type="text" ref="searchBox" @input="() => filterDataMethod(customSearch)" placeholder="Filter">
                </div>
                <div v-if="filterType.indexOf('dropdown')>-1" :class="`input-group ${filterType.length === 2?'split-40':''}`">
                    <div class="select-wrapper">
                        <select v-model="selectedFilterValue" @change="() => filterDataMethod(customSearch)">
                            <option
                                v-for="filterChoice in filterChoices"
                                :key="filterChoice.value"
                                :value="filterChoice"
                            >{{ filterChoice.label }}</option>
                        </select>
                    </div>
                </div>
                <ul v-if="filterType.indexOf('tags')>-1" :class="`input-group ${filterType.length === 2?'split-40':''}`" class="tabs">
                    <li v-for="filterChoice in filterChoices" :key="filterChoice.value">
                        <a
                            href="javascript:"
                            @click="redirect(filterChoice.value)"
                            :class="selectedFilterValue.value === filterChoice.value? 'active' : ''"
                        >
                            {{ filterChoice.label }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
</template>

<script>
export default {
    name: "AppHeader",
    props: {
        search: String,
        filterType: {
            type: Array,
            default: () => ['search']
        },
        filterChoices: {
            type: Array,
            default: () => []
        },
        filterObject: {
            type: String,
            default: ''
        },
        filterData: [Object,Array],
        customSearch: {
            type: [Function, Boolean],
            default: false
        }
    },
    data() {
        window.jQuery('title').text(`${window.jQuery('title').text()} - ${this.$slots.default[0].text}`)
        return {
            teamId: this.Team,
            selectedFilterValue: {}
        }
    },
    mounted() {
        this.selectFilter();
        this.$emit('input', this.filterData)
    },
    watch: {
        filterChoices() {
            this.selectFilter()
        }
    },
    methods: {
        selectFilter() {
            let selectedFilterValue = {}
            for (const index in this.filterChoices) {
                if (this.filterChoices[index].selected) {
                    selectedFilterValue = this.filterChoices[index];
                    break;
                }
            }
            this.selectedFilterValue = selectedFilterValue
        },
        filterDataMethod(customSearch) {
            if (!!customSearch !== false) {
                let searchBoxValue = ""
                if (typeof this.$refs.searchBox === 'object') {
                    searchBoxValue = this.$refs.searchBox.value;
                }
                if (this.selectedFilterValue){
                    return customSearch(this.filterDataMethod,searchBoxValue,this.selectedFilterValue.value);
                }
            }
            let filter = "";
            if (typeof this.$refs.searchBox === 'object') {
                filter = this.$refs.searchBox.value.toLowerCase();
            }
            if (filter === '') {
                this.$emit('input', this.filterData)
            }
            let data = this.filterData
            const words = filter.split(' ');
            for (const index in words) {
                data = this.filterObjects(words[index], data)
            }
            this.$emit('input', data)
        },
        filterObjects(filter, filterData) {
            const data = [];
            let objArr = []
            if (this.filterObject !== '') {
                objArr = this.filterObject.split(',')
            }
            for (const index in filterData) {
                const filterObjects = [filterData[index]]
                for (const i in objArr) {
                    filterObjects.push(filterData[index][objArr[i]])
                }
                for (const i in filterObjects) {
                    const result = this.filter(filterObjects[i], filter, filterData[index])
                    if (result) {
                        data.push(result)
                        break
                    }
                }
            }
            return data
        },
        filter(filterObject, filter, item) {
            for (const field in filterObject) {
                if (typeof filterObject[field] === 'string' &&
                    filterObject[field].toLowerCase().search(filter)>-1) {
                    return item
                }
            }
            return false
        },
        redirect(filterChoice) {
            let param = 'type';
            if (this.filterChoices[0].value === 'active') {
                param = 'date';
            }

            let search = '';
            let urlSearchParams = new URLSearchParams(window.location.search);
            let params = Object.fromEntries(urlSearchParams.entries());

            Object.keys(params).forEach(function(key, i) {
                search += i == 0 ? '?' : '&';
                search += (key == param) ? `${key}=${filterChoice}` : `${key}=${params[key]}`;
            });

            if(! search.includes(param)) {
                search += search === "" ? `?`: `&`;
                search += `${param}=${filterChoice}`;
            }
            window.location.href = `${window.location.origin + window.location.pathname}${search}`;
        }
    }
}
</script>

<style scoped>

</style>
