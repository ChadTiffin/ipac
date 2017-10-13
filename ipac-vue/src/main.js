// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import Ajax from './ajax'
import CommonFetches from './commonFetches'
import routes from './routes'
import VueClipboard from 'vue-clipboard2'

Vue.config.productionTip = false

Vue.mixin(Ajax)
Vue.mixin(CommonFetches)

Vue.use(VueClipboard);

if (!("apiBase" in window))
  window.apiBase = "https://ipac-api.chadtiffin.com/"

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  template: '<App />',
  components: { App },
  data() {
  	return {
  		routes: routes,
  		isOffline: false,
      userFullName: "",
      mobile: true,
      mobileBreakpoint: 992,
      users: []
	  }  		
  },
  methods: {
    getWindowWidth() {

    }
  },
  created() {
    let vm = this

    if ("userDetails" in localStorage) {
      let user = JSON.parse(localStorage.userDetails)

      this.userFullName = user.first_name + " " + user.last_name
    }
    else
      this.userFullName = "You"

    if (window.innerWidth < vm.mobileBreakpoint)
      vm.mobile = true
    else
      vm.mobile = false

    if ("apiKey" in localStorage)
      this.fetchUsers()

    window.addEventListener('resize', function() {

      if (window.innerWidth < vm.mobileBreakpoint)
        vm.mobile = true
      else
        vm.mobile = false
    });
  }
})
