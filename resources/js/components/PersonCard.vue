<template>

    <div class="card person caregiver" @click="toggleCard">
        <div class="details">
            <div class="preview">{{ person.Firstname.trim().charAt(0) }}{{ person.Lastname.trim().charAt(0) }}</div>

            <div class="details-info">
                <p class="title">{{ person.Firstname }} {{ person.Lastname }} <span v-if="person.IsPersonalClient">⭐</span></p>
            </div>

            <i>expand_more</i>
        </div>

        <ul class="content details-with-icons">
            <li v-if="person.teamName && Caregiver"><i>people</i><span>{{ person.teamName }}</span></li>
            <li v-if="person.Mail"><i>email</i><span>{{ person.Mail }}</span></li>
            <li><i>phone</i><a :href="`tel:${ person.PhoneNumber }`">{{ person.PhoneNumber }}</a></li>

            <li v-if="person.AddressStreet">
                <i>place</i>
                <a target="_blank" :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
                    `${person.AddressStreet} ${person.AddressNumber}, ${person.AddressZipcode} ${person.AddressCity}`
                )}`">
                    {{ person.AddressStreet }}
                    {{ person.AddressNumber }},
                    {{ person.AddressZipcode }}
                    {{ person.AddressCity }}
                </a>
            </li>

            <li v-if="typeof person.AmountOfPersonalCareGivers !== 'undefined'">
                <i>local_hospital</i>
                <span>
                    {{ person.AmountOfPersonalCareGivers }} regievoerders (<a :href="`personalCaregiver?Id=${ person.Id }`">Aanpassen</a>)
                </span>
            </li>

        </ul>
    </div>
</template>

<script>
    export default {
        name: "PersonCard",
        props: {
            person: Object,
            subtitle: String,
            buttons: Object,
            removeButton: {
                type: String,
                default: ''
            },
            Caregiver: {
                Boolean,
                default: false
            },
        },
        data() {
            return {
               show: false
            };
        },
        methods: {
            toggleCard(event) {
                window.toggleCard(event, this.$el);
            },
        }
    }
</script>
<style scoped>
</style>

<style scoped>
    .slide-enter-active, .slide-leave-active {
        transition: all .3s ease-out;
        overflow: hidden;
    }

    /*
    you set the css property before transition starts
    */
    .slide-enter, .slide-leave-to {
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
        transform: scaleY(0);
    }

    /*
    you set the css property it will be when transition ends
    */
    .slide-enter-to, .slide-leave {
        max-height: 180px;
        padding-top: 16px;
        padding-bottom: 16px;
        transform: scaleY(1);
    }
</style>
