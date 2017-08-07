// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import Ajax from './ajax'
import routes from './routes'

Vue.config.productionTip = false

Vue.mixin(Ajax)

window.apiBase = "https://ipac-api.chadtiffin.com/"


/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  template: '<App/>',
  components: { App },
  data() {
  	return {
  		routes: routes
	}  		
  }
})
