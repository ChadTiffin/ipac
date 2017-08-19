export default {
	methods: {

		fetchLocations() {
			let vm = this

			let order = JSON.stringify(["location_name","ASC"]);

			this.getJSON(window.apiBase + "location/get?order="+order).then(function(response){
				if (!("status" in response)) { //not offline
					vm.locations = response
					localStorage.locations = JSON.stringify(response)
				}
				else {
					vm.locations = JSON.parse(localStorage.locations)
					vm.$root.isOffline = true
				}

			})
		},
		fetchAuditTemplates() {
			let vm =this

			this.getJSON(window.apiBase + "AuditFormTemplate/get").then(function(response){

				if ("status" in response && response.status == "offline") {
					if (localStorage.auditTemplates) {
						vm.auditTemplates = JSON.parse(localStorage.auditTemplates)
					}
					vm.$root.isOffline = true
				}
				else {
					vm.auditTemplates = response
					localStorage.auditTemplates = JSON.stringify(response)
				}
			})
		},
		fetchClients() {
			let vm = this

			this.getJSON(window.apiBase + "client/get").then(function(response){
				if (response.status == "offline") {
					//try to pull from localstorage
					if (localStorage.clients) {
						vm.clients = JSON.parse(localStorage.clients)
					}
					vm.$root.isOffline = true
				}
				else {
					vm.clients = response
					localStorage.clients = JSON.stringify(response)
					vm.$root.isOffline = false
				}

				vm.$emit("toggleSpinner",false)
			})
		},
	}
}