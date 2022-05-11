<template>
    <div v-else class="card" :class="getCardClass(appointment)" @click="toggleCard">
        <div class="header">
            <span class="date" v-if="! isReportView && appointment.shouldReport">Today</span>
            <span class="date" v-else v-bind:class="isReportView ? 'simple' : ''">{{ questionStatus(appointment).label }}</span>

            <template>
                <span>
                    {{ showDate ? `${(new Intl.DateTimeFormat('nl-NL', {weekday:'short'})).format(new Date(appointment.datetimeFrom))}, ${(new Intl.DateTimeFormat('en-GB', {month: '2-digit', day: '2-digit', year: 'numeric'})).format(new Date(appointment.datetimeFrom))},` :""}}
                    {{ localHours(appointment.datetimeFrom) }} - {{ localHours(appointment.datetimeTo) }}
                </span>
            </template>

            <i>expand_more</i>
        </div>

        <div class="details">
            <div :class="`preview icons ${ appointment.question.type }`"></div>
            <div class="details-info">
                <p class="title">{{ appointment.question.client.firstname }} {{ appointment.question.client.lastname }}</p>
                <p>{{ types[appointment.question.type] }} {{ !caregiverAgenda?`met ${ appointment.careGiver.firstname } ${ appointment.careGiver.lastname }`:''}} </p>
            </div>
        </div>

        <ul class="content details-with-labels no-border">
            <li v-if="appointment.report">
                <div class="label">Explanation:</div>
                <div class="with-wrap">{{ appointment.report }}</div>
            </li>
            <li v-if="appointment.cancelReason">
                <div class="label">Reason for canceling:</div>
                <div class="with-wrap">{{ appointment.cancelReason }}</div>
            </li>
            <li>
                <div class="label">Team</div>
                <div>{{ appointment.question.client.team }}</div>
            </li>
            <li>
                <div class="label">Categories</div>
                <div>{{ appointment.question.category }}</div>
            </li>

            <li>
                <div class="label">Question</div>
                <div class="with-wrap">{{ appointment.question.question }}</div>
            </li>
            <li v-if="appointment.selectedRoles">
                <div class="label">Netwerk</div>
                <div>{{ appointment.selectedRoles }}</div>
            </li>
            <li v-else-if="!caregiverAgenda">
                <div class="label">telephone number</div>
                <div>{{appointment.question.client.phone}}</div>
            </li>
            <template>
                <hr>
                <li>
                    <div class="label">telephone number</div>
                    <a :href="`tel:${appointment.question.client.phone}`">{{appointment.question.client.phone}}</a>
                </li>

                <li v-if="appointment.question.client.email">
                    <div class="label">E-mailadres</div>
                    <a :href="`mailto:${appointment.question.client.email}`">{{appointment.question.client.email}}</a>
                </li>
                <li v-if="appointment.question.client.address.street">
                    <div class="label">Adres</div>
                    <a target="_blank" :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${
                      appointment.question.client.address.street
                    } ${appointment.question.client.address.number}, ${appointment.question.client.address.zipcode
                    } ${appointment.question.client.address.city}`)}`">
                        {{ appointment.question.client.address.street }}
                        {{ appointment.question.client.address.number }},
                        {{ appointment.question.client.address.zipcode }}
                        {{ appointment.question.client.address.city }}
                    </a>
                </li>
            </template>

            <li v-if="caregiverAgenda">
                <a v-if="appointment.shouldReport" href="javascript:" @click="$emit('click', appointment)" class="button">Report Appointment</a>
                <a v-else-if="showCancel(appointment)" href="javascript:" @click="$emit('cancel', appointment)" class="button alt">Cancel appointment</a>
            </li>
        </ul>
    </div>
</template>

<script>
    const StateColors = {
      created: "orange",
      completed: "green",
      failedNoShow: "purple",
      failedNoOther: "purple"
    }
    export default {
        name: "AgendaCard",
        props: {
            appointment: Object,
            showDate: {
              type: Boolean,
              default: false
            },
            accountType: String,
            activeEvent: String,
            caregiverAgenda: {
                type: Boolean,
                default: false
            },
            isReportView: {
                type: Boolean,
                default: false
            },
        },
        data() {
            return {
                show: false,
                types: {
                    call: 'Calling',
                    visit: 'Home visit'
                }
            };
        },
        methods: {
            getCardClass(appointment) {
                const prefix = "";

                if (appointment.shouldReport !== true) {
                    if (appointment.state !== "completed") {
                        return prefix + StateColors[appointment.state];
                    }

                    return StateColors[appointment.state];
                }

                return prefix + "red";
            },
            toggleCard(event) {
                window.toggleCard(event, this.$el);
            },
            localHours(date) {
                const dateJs = new Date(date);
                const minutes = `${dateJs.getMinutes()}`.padStart(2,'0');
                return `${dateJs.getHours()}:${minutes}`
            },
            questionStatus(appointment) {
                return {
                    class: this.getCardClass(appointment),
                    label: appointment.question.state
                }
            },
          showCancel(appointment) {
            return (this.accountType==='caregiver' && appointment.state === 'created' && new Date(appointment.datetimeFrom) > new Date())
          }
        }
    }
</script>
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
