<template>
    <div>
        <modal v-if="cancelAppointmentItem" @click="cancelAppointment"
               :buttons="modal.buttons">
            Are you sure you want to cancel this meeting?
        </modal>
        <div v-if="!chosenAppointment" class="split-col split-40-60">
            <div class="col agenda-date-picker">
                    <v-app id="inspire">
                            <v-date-picker
                                locale="nl"
                                first-day-of-week="1"
                                header-color="#6d0eb1"
                                :picker-date.sync="pickerDate"
                                is-inline
                                :events="attributes"
                                v-model="selectedDate"
                                is-expanded
                            />
                    </v-app>
            </div>
            <div class="col">
                <div v-if="loading" class="empty-list">
                    <h6>Loading...</h6>
                </div>
                <div v-else>
                    <empty-state v-if="events.length === 0" :asset-url="this.asseturl">
                        There are no scheduled appointments on this date
                    </empty-state>
                    <agenda-card
                            @click="showWriteReport"
                            @cancel="showCancelConfirm"
                            v-for="appointment in events"
                            :caregiver-agenda="type==='caregiver'"
                            :is-report-view="false"
                            account-type="caregiver"
                            :key="appointment.id"
                            :appointment="appointment"
                    ></agenda-card>
                </div>
            </div>
        </div>
        <div v-else-if="type==='caregiver'">
            <report-form
                    :appointment="chosenAppointment"
                    @back="chosenAppointment = false"
                    @save="(data) => $emit('save-report', { data, closeForm: () => chosenAppointment = false })"
            ></report-form>
        </div>

    </div>
</template>

<script>
    export default {
        name: "Agenda",
        props: {
            loading: false,
            appointments: Array,
            attributes: Array,
            asseturl: String,
        },
        data() {
            return {
                pickerDate: null,
                selectedDate: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                chosenAppointment: false,
                cancelAppointmentItem: false,
                modal: {
                  buttons: [{ label: 'Yes', class: 'alt' },{ label: 'Cancel' }]
                },
            }
        },
        watch: {
            pickerDate(val){
               this.$emit("picker-date", val)
            },
            selectedDate(value) {
                this.$emit('input', value);
            }
        },
        computed: {
            events: function () {
                let result = [];
                if (typeof this.appointments !== 'undefined') {
                    if (this.appointments.length >= 1) {
                        result = result.concat(this.appointments);
                    }
                }
              return this.sortByDate(result);
            }
        },
        methods: {
            sortByDate(events, order = 'ASC') {
              let operator = (order == "ASC") ? ">" : "<";
              events.sort((a, b) => {
                if (operator == ">") {
                  return (Date.parse(a.datetimeFrom) < Date.parse(b.datetimeFrom)) ? -1 : 1;
                } else {
                  return (Date.parse(a.datetimeFrom) > Date.parse(b.datetimeFrom)) ? -1 : 1;
                }
              });
              return events;
            },
            showWriteReport(appointment) {
                this.chosenAppointment = appointment;
            },
            showCancelConfirm(appointment) {
                this.cancelAppointmentItem = appointment;
                this.isModalVisible = true;
            },
            cancelAppointment(value) {
                if (value === 'Yes') {
                    this.modal.buttons = [{label: 'Wait a second', class: 'disabled'}, {label: 'Cancel'}];
                    axios.post('/agenda/caregiver/cancelAppointment', {
                        appointmentId: this.cancelAppointmentItem.id
                    })
                        .then((response) => {
                            if (response.data.success) {
                                const date = new Date(this.cancelAppointmentItem.datetimeFrom)
                                const year = new Intl.DateTimeFormat('en', {year: 'numeric'}).format(date)
                                const month = new Intl.DateTimeFormat('en', {month: '2-digit'}).format(date)
                                this.$emit('canceled-appointment', this.appointments.filter(appointment => appointment.id !== this.cancelAppointmentItem.id), {
                                    year,
                                    month
                                });
                                this.modal.buttons = [{label: 'Yes', class: 'alt'}, {label: 'Cancel'}];
                                this.cancelAppointmentItem = false;
                                const dateMonth = this.pickerDate+"-01"
                                this.$emit('picker-date', dateMonth)
                            }
                        })
                } else {
                    this.cancelAppointmentItem = false
                }
            }
        }
    }
</script>

<style scoped>

</style>
