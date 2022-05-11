<template>
    <div class="card question">
        <modal v-if="showModal" @click="assignCareGiver" :buttons="modal.buttons">
            {{ modal.message }}
        </modal>

        <div v-else class="header">
            <span class="date">{{ `${(new Intl.DateTimeFormat('nl-NL', {weekday:'short'})).format(new Date(question.DateTimeFrom))}, ${(new Intl.DateTimeFormat('en-GB', {month: '2-digit', day: '2-digit', year: 'numeric'})).format(new Date(question.DateTimeFrom))}` }}</span>
            <span>{{ `${(new Intl.DateTimeFormat('en-GB', { hour: 'numeric', minute: 'numeric'})).format(new Date(question.DateTimeFrom))}` }} - {{ `${(new Intl.DateTimeFormat('en-GB', { hour: 'numeric', minute: 'numeric'})).format(new Date(question.DateTimeTo))}` }}</span>
        </div>

        <div class="details">
            <div class="preview icons" :class="question.AppointmentType"></div>

            <div class="details-info">
                <p class="title">{{ client.Firstname }} {{ client.Lastname }}</p>
                <p>{{ types[question.AppointmentType] }}</p>
            </div>
        </div>

        <div class="content no-padding">
            <ul class="details-with-labels">
                <li>
                    <div class="label">Question</div>
                    <div class="with-wrap">{{ question.Description }}</div>
                </li>
                <li>
                    <div class="label">Categories</div>
                    <div>{{ question.Categories }}</div>
                </li>
                <li v-if="question.Team">
                    <div class="label">Team</div>
                    <div>{{ question.Team }}</div>
                </li>
                <li v-if="question.AssignedRoles">
                    <div class="label">Netwerk</div>
                    <div>{{ question.AssignedRoles }}</div>
                </li>

                <li v-if="client.AddressStreet">
                    <div class="label">Adress</div>
                    <div>
                        <a target="_blank" :href="`https://www.google.com/maps/search/?api=1&query=${
                            encodeURIComponent(`${client.AddressStreet} ${client.AddressNumber}, ${client.AddressZipcode} ${client.AddressCity}`)
                        }`">
                            {{ client.AddressStreet }}
                            {{ client.AddressNumber }},
                            {{ client.AddressZipcode }}
                            {{ client.AddressCity }}
                        </a>
                    </div>
                </li>

                <li>
                    <div class="label">Telefoonnummer</div>
                    <div>
                        <a :href="`tel:${client.PhoneNumber}`">{{ client.PhoneNumber }}</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
export default {
  name: 'HelloWorld',
  props: {
    question: Object,
    client: Object,
    careGivers: Object,
    date: String,
    csrf: String,
    workingHours: Object,
    types: {
      type: Object,
      default: () => ({
        visit: "Home visit",
        call: "Mobile call"
      })
    }
  },
  data() {
      const selectedTime = this.date.split(':');
      return {
      selectedDate:  new Date(this.date),
      selectedHour: selectedTime[0].slice(-2),
      selectedMinute: selectedTime[1],
      show: false,
      isExpanded: false,
      selectedCareGiver: "",
      hasCaregiverError: false,
      modal: {
        message: "",
      },
      showModal: false
    }
  },
  mounted() {
    if (this.careGivers.Id) {
      this.selectedCareGiver = this.careGivers.Id
    }
  },
  watch: {
    selectedCareGiver(value) {
      this.hasCaregiverError = value==="";
    }
  },
  computed: {
    isModified() {
      const date = this.selectedDate.getFullYear() + '-' +
          ('00' + (this.selectedDate.getMonth()+1)).slice(-2) + '-' +
          ('00' + this.selectedDate.getDate()).slice(-2) + ' ' +
          this.selectedHour + ':' + this.selectedMinute+ ':00';
      return Date.parse(date) !== Date.parse(this.date);
    },
    formattedDate() {
      return Intl.DateTimeFormat('nl-NL').format(this.selectedDate);
    },
    workingHoursField() {
      const currentDay = this.selectedDate.getDay()?this.selectedDate.getDay():7;
      let startHour = 0;
      let endHour = 24;
      if (this.workingHours[this.question.AppointmentType][currentDay]) {
          startHour = this.workingHours[this.question.AppointmentType][currentDay][0].split(':')[0];
          endHour = this.workingHours[this.question.AppointmentType][currentDay][1].split(':')[0]
      }
      const hours = [];
      for (let i=0;i<24;i++) {
        hours.push({
          hour:`${i}`.padStart(2,'0'),
          color: startHour>i || endHour<=i? 'red':'black'
        });
      }
      return hours
    }
  },
  methods: {
    expand() {
      this.isExpanded = true;
    },
    submitData() {
      let confirmText = ""
      if (this.selectedCareGiver === "") {
        this.hasCaregiverError = true;
        return false;
      }
      if (confirmText) {
        this.modal.message = confirmText;
        this.modal.buttons = [{ label: 'Yes', class: 'alt' },{ label: 'Cancel' }]
        this.showModal = true;
        return false;
      }
      this.assignCareGiver('Yes');
    },
  }
}
</script>
