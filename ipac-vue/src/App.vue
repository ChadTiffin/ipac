<template>
  <div id="app">
    <router-view v-if="!loggedIn" v-on:toggleSpinner="toggleSpinner"></router-view>

    <div class="dashboard" v-else>

      <DashboardHeader
        :menu-showing="menuShowing"
        :alert="alert"
        v-on:navigate="navigate"
        v-on:toggleMenu="menuShowing ? menuShowing = false : menuShowing = true"
        >
      </DashboardHeader>

      <div class="alert main-alert" :class="alert.class" v-if="alert.visible">
        <i class="fa" :class="alert.icon"></i>
        {{ alert.msg }}
        <p v-if="alert.errors">{{ alert.errors }}</p>
      </div>

      <div class="section-wrapper" :class="{ menuShowing: menuShowing }" v-on:click="hideMenu">
        
        <router-view 
          v-on:updateAlert="updateAlert" 
          v-on:updateLoginStatus="updateLoginStatus" 
          v-on:toggleSpinner="toggleSpinner" 
          v-on:clientsChanged="fetchClients"
          :clients="clients">
        </router-view>
        
      </div>
    </div>
    <spinner v-if="spinnerVisible"></spinner>
  </div>
</template>

<script>
import DashboardHeader from './components/DashboardHeader'
import Spinner from './components/Spinner'

export default {
  name: 'app',
  components: {
    DashboardHeader,
    Spinner
  },
  data() {
    return {
      loggedIn: false,
      menuShowing: true,
      alert: {
        visible: false,
        msg: "",
        class: "",
        errors: []
      },
      spinnerVisible: true,
      clients: []
    }
  }, 
  watch: {
    '$route': function() {
      if (window.innerWidth <= 1400)
          this.menuShowing = false
    }
  },
  methods: {
    navigate(location) {
      this.$router.push(location)

      this.currentRoute = location

      if (window.innerWidth <= 1400)
        this.menuShowing = false
      
    },
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
    fetchClients() {
      let vm = this

      this.getJSON(window.apiBase + "client/get").then(function(response){
        vm.clients = response

        vm.$emit("toggleSpinner",false)
      })
    },
  },
  created() {
    if ("apiKey" in localStorage) {
      this.loggedIn = true
      this.fetchClients()
    }
    else
      this.loggedIn = false
    
  }
}
</script>

<style>

  .section-wrapper {
    margin-top: 40px;
    overflow: hidden;

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

  h2 {
    font-size: 16pt;
    margin-top: 10px;

  }

  .button-bar {
    background-color: #dbe3e4;
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

  @media (max-width: 1200px) {
    .section-wrapper.menuShowing {
      margin-left: 0;
    }
  }

  @media (max-width: 500px) {
    section {
      padding: 10px;
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
