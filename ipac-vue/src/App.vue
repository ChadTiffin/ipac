<template>
  <div id="app">
    <router-view v-if="!loggedIn" v-on:toggleSpinner="toggleSpinner"></router-view>

    <div class="dashboard" v-else>

      <DashboardHeader
        :menu-showing="menuShowing"
        :alert="alert"
        v-on:toggleMenu="menuShowing ? menuShowing = false : menuShowing = true"
        v-on:newAudit="auditDialog.visible = true"
        :is-offline="$root.isOffline"
        :updateAvailable="updateAvailable"
        :page-title="pageTitle"
        >
      </DashboardHeader>

      <transition
        name="modalOverlayFade"
        enter-active-class="fadeIn"
        leave-active-class="fadeOut" >

        <div class="alert main-alert" :class="alert.class" v-if="alert.visible">
          <i class="fa" :class="alert.icon"></i>
          {{ alert.msg }}
          <p v-if="alert.errors">{{ alert.errors }}</p>
        </div>
      </transition>

      <div class="section-wrapper" :class="{ menuShowing: menuShowing }" v-on:click="hideMenu">
        
        <router-view 
          v-on:updateAlert="updateAlert" 
          v-on:updateLoginStatus="updateLoginStatus" 
          v-on:toggleSpinner="toggleSpinner" 
          v-on:clientsChanged="fetchClients"
          v-on:newAudit="newAudit"
          v-on:pageTitle="setPageTitle"

          :is-offline="$root.isOffline"
          :clients="clients">
        </router-view>
        
      </div>
    </div>
    <spinner v-if="spinnerVisible"></spinner>

    <modal-dialog
      v-if="auditDialog.visible" 
      :title="auditDialog.title" 
      :modal-visible="auditDialog.visible" 
      :confirm-button-text="auditDialog.buttonText"
      :button-class="auditDialog.buttonClass"
      v-on:confirm="createNewAudit"
      v-on:closeModal="auditDialog.visible = false">

      <form-group label="Client" col-class="col-md-4">
        <select class="form-control" v-model="auditDialog.fields.client_id" required>
          <option v-for="client in clients" :value="client.id" >{{ client.company }}</option>
        </select>
      </form-group>
      
      <form-group label="Location" col-class="col-md-4">
        <select class="form-control" v-model="auditDialog.fields.location_id" required>
          <option v-for="location in selectedClientLocations" :value="location.id" >{{ location.location_name }}</option>
        </select>
      </form-group>

      <form-group label="Audit Date" col-class="col-md-4">
        <date-field extra-classes='form-control' v-model="auditDialog.fields.audit_date" ></date-field>
      </form-group>

      <form-group label="Form Template" col-class="col-md-4">
        <select class="form-control" v-model="auditDialog.fields.form_template_id" required>
          <option v-for="template in auditTemplates" :value="template.id">{{ template.form_name }}</option>
        </select>
      </form-group>

    </modal-dialog>

    <div v-if="$root.isOffline" class="offline-flag bg-danger">
      <i class="fa fa-plug"></i>
      You appear to be offline. The app is running in a limited state
    </div>
  </div>
</template>

<script>
import DashboardHeader from './components/DashboardHeader'
import Spinner from './components/Spinner'
import ModalDialog from './components/ModalDialog'
import FormGroup from './components/FormGroup'
import DateField from './components/DateField'
import bus from './bus.js'

export default {
  name: 'app',
  components: {
    DashboardHeader,
    Spinner,
    ModalDialog,
    FormGroup,
    DateField
  },
  data() {
    return {
      updateAvailable: false,
      loggedIn: false,
      menuShowing: true,
      pageTitle: {
        mainTitle: this.$route.meta.titleText,
        subTitle: false
      },
      alert: {
        visible: false,
        msg: "",
        class: "",
        errors: []
      },
      spinnerVisible: true,
      clients: [],
      locations: [],
      users: [],
      recentAudits: [],
      auditDialog: {
        visible: false,
        title: "New Audit",
        buttonText: "Create",
        buttonClass: "btn-success",
        fields: {
          client_id: null,
          location_id: null,
          audit_date: null,
          form_template_id: null
        }
      },
      auditTemplates: []
    }
  }, 
  watch: {
    '$route': function() {
      if (window.innerWidth <= 1400)
          this.menuShowing = false

      this.pageTitle = {
        mainTitle: this.$route.meta.titleText,
        subTitle: false
      }
    }
  },
  computed: {
    selectedClientLocations() {

      let locationSubset = []
      let vm = this

      this.locations.forEach(function(location, index){
        if (location.client_id == vm.auditDialog.fields.client_id) {
          locationSubset.push(location)
        }
      })

      return locationSubset
    }
  },
  methods: {
    hideMenu() {
      if (window.innerWidth < 1050 && this.menuShowing)
        this.menuShowing = false
    },
    updateAlert(alert) {
      this.alert = alert
      let vm = this

      setTimeout(function(){
        vm.alert.visible = false
      },4000)
    },
    updateLoginStatus(newStatus) {
      this.loggedIn = newStatus
    },
    toggleSpinner(visibility) {
      this.spinnerVisible = visibility
    },
    setPageTitle(title) {
      this.pageTitle = title
    },
    newAudit() {
      this.getAuditTemplates()
      this.auditDialog.visible = true
    },
    createNewAudit() {
      let vm = this

      this.postData(window.apiBase+"auditForm/save",this.auditDialog.fields).then(function(response){
        vm.auditDialog.visible = false  

        bus.$emit("auditsChanged") 

        if ("status" in response && response.status == "offline") {
          //save new audit to localstorage

          let offlineAudits = []

          if (localStorage.offlineAudits) {
            offlineAudits = JSON.parse(localStorage.offlineAudits)
          }

          let hashCode = function(s){
            return s.split("").reduce(function(a,b){a=((a<<5)-a)+b.charCodeAt(0);return a&a},0);              
          }

          let id = Math.abs(hashCode(JSON.stringify(vm.auditDialog.fields)));

          vm.auditDialog.fields.id = "temp-"+id

          offlineAudits.push(vm.auditDialog.fields);

          localStorage.offlineAudits = JSON.stringify(offlineAudits)
        }

      })
    },
    syncLocalStorage() {
      console.log("Checking local storage...")
      if (localStorage.offlineAudits) {
        console.log("Sync required")

        let audits = JSON.parse(localStorage.offlineAudits)

        //strip temp ids
        audits.forEach(function(audit, index){
          if (audit.id.search("temp-") >= 0)
            delete audit.id
        })

        if (audits.length > 0) {

          let payload = {
            records: JSON.stringify(audits)
          }

          let vm = this

          this.postData(window.apiBase+"auditForm/save-batch",payload).then(function(response){
            if (response.status == "success") {
              localStorage.removeItem("offlineAudits");

              vm.alert.visible = true
              vm.alert.icon = "fa-plug"
              vm.alert.class = "alert-success"
              vm.alert.msg = "Locally saved audits have been synced with the server";
              vm.alert.errors = false
            }
          })
        }
      }

    },
    /*fetchRecentAudits($days_back) {
      let vm = this

      let filters = "";
      if (this.searchTerms) {
        filters = JSON.stringify([
          ["location_name",this.searchTerms,'and','like'],
          ["company",this.searchTerms,'or','like'],
          ["audit_date",this.searchTerms,'or','like'],
          ["form_name",this.searchTerms,'or','like']
        ])
      }
      else {

        //get date $days_back days ago
        var today = new Date()
        var priorDate = new Date().setDate(today.getDate()-$days_back)

        priorDate = new Date(priorDate)
        var date_string = priorDate.getFullYear()+"-"+(priorDate.getMonth()+1)+"-"+priorDate.getDate()

        filters = JSON.stringify([
          ["audits.updated_at >=",date_string]
        ])
      }

      this.getJSON(window.apiBase + "auditForm/get?filters="+filters).then(function(response){
        vm.recentAudits = response
        localStorage.recentAudits = response

      })
    }*/
    checkAppVersion()
    {
      let vm = this

      this.getJSON(window.apiBase + "tools/app-version").then(function(response){
        if (!("appVersion" in localStorage)) {
          vm.updateAvailable = true
        }
        else {
          if (localStorage.appVersion != response.version && !vm.$root.isOffline) 
            vm.updateAvailable = true
        }
      })
    }
  },
  created() {

    if ("apiKey" in localStorage) {
      this.loggedIn = true
      this.fetchClients()
      this.fetchAuditTemplates()
      this.fetchLocations()
      //this.fetchRecentAudits(7)

      this.syncLocalStorage()
      this.checkAppVersion()
    }
    else
      this.loggedIn = false

    let vm = this

    bus.$on("updateAlert", function(params){
      vm.updateAlert(params)
    })
    
  }
}
</script>

<style>

html {
  background-color: 
}

body {
  background-color: transparent;
}

.btn, .form-control {
  border-radius: 20px
}

.v-select .dropdown-toggle {
  position: relative;
  background-color: white;
}

  .section-wrapper {
    margin-top: 47px;
  }

  .section-outer {
    margin: auto;
    background-color: white;
  }

  section {
    padding: 20px;
    margin: auto;
    
    margin-bottom: 20px;
    background-color: white;
    position: relative;
  }

  section.small-section {
    max-width: 600px;
    margin-left: 0;
  }

  h1 {
    font-size: 22pt;
    margin-top: 10px;
    margin-bottom: 0;
    font-family: 'Roboto', sans-serif;
  }

  h2 {
    font-size: 18pt;
    margin-top: 10px;
    text-transform: uppercase;
    color: #424546;
    font-family: 'Roboto', sans-serif;

  }

  h3 {
    margin: 0;
    margin-bottom: 10px;
    text-transform: uppercase;
    font-size: 14pt;
    color: #424546;
    font-family: 'Roboto', sans-serif;
  }

  h4 {
    font-size: 12pt;
    font-weight: bold;
    color: #515556;
  }

  hr {
    margin-top: 10px;
    margin-bottom: 20px;
  }

  .button-bar {
    background-color: #006477;
    margin: -20px;
    margin-bottom: 20px;
    z-index: 3;
    padding: 5px;
    color: white;
  }

  .button-bar a.router-link {
    margin-top: 8px;
    margin-left: 10px;
    margin-bottom: 5px;
    display: inline-block;
    cursor: pointer;
  }

  .button-bar a {
    color: white;
  }

  .button-bar a.btn {
    margin-bottom: -1px;
  }

  .btn.block-button {
    display: block;
    width: 100%;
    margin-bottom: 5px;
  }

  .table {
    margin-bottom: 0;
  }

  .row section {
    margin-bottom: 0;
    padding-bottom: 5px;
  }

  .col-amounts {
    width: 100px;
  }

  .icon-button {
    font-size: 22px;
    cursor: pointer;
  }

  .icon-button:hover {
    opacity: 0.8;
  }

  .icon-button.fa-remove {
    color: #b70000;
  }

  .icon-button.fa-plus {
    color: green;
  }

  .alert {
    margin-bottom: 5px;
  }

  .bg-outflows {
    background-color: #fce8e3;
  }

  input[type=number], td.number, th.number {
    text-align: right;
  }

  .table>tbody>tr>td.td-indent {
    padding-left: 15px;
  }

  tbody>tr>td.has-control, tbody>tr>th.has-control {
    padding-top: 1px;
    padding-bottom: 1px;
  }

  td.btn-col, th.btn-col {
    width: 30px;
  }

  thead .btn-col {
    width: 60px;
  }

  .table>thead>tr>th, .table>thead>tr>td, .table>tbody>tr>td {
    vertical-align: middle;
  }

  .table .fa-remove:hover {
    color: red;
  }

  tr:hover .fa-remove {
    display: inline-block;
  }

  .offline-flag {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 5px;
    text-align: center;
    z-index: 5;
  }

  .well {
    padding: 10px;
  }

  @media (max-width: 1200px) {
    .section-wrapper.menuShowing {
      margin-left: 0;
    }
  }

  @media (max-width: 500px) {
    section {
      padding: 10px;
    }

    .button-bar {
      margin: -10px;
      margin-bottom: 10px;
    }

    .col2-control {
      display: block;
      width: 100%;
    }

    .vdp-datepicker {
      display: block;
      width: 100%;
    }
  }

</style>
