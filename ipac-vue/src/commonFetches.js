export default {
	computed: {
		fullName() {
			if ("userDetails" in localStorage) {
				let user = JSON.parse(localStorage.userDetails)

				return user.first_name +" " + user.last_name
			}
			else 
				return "You"
		}
	},
	methods: {
		fetchUsers() {
			let vm = this

			let order = JSON.stringify(["first_name","ASC"])

			this.getJSON(window.apiBase+"user/get?order="+order).then(function(response){
				if (!("status" in response)) { //not offline
					vm.$root.users = response
					localStorage.users = JSON.stringify(response)
				}
				else {
					vm.$root.users = JSON.parse(localStorage.users)
					vm.$root.isOffline = true
				}
			})
		},
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
		getAuditTemplates() {
			if ("auditTemplates" in localStorage) 
				this.auditTemplates = JSON.parse(localStorage.auditTemplates)
			else
				this.fetchAuditTemplates()
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
		searchClients(terms) {
			let vm = this

			let order = JSON.stringify(["company","ASC"]);

			let filters = JSON.stringify([
				["company",terms,"or","like"],
				["contacT_name",terms,"or","like"],
				["email",terms,"or","like"],
				["city",terms,"or","like"]
			])

			vm.$emit("toggleSpinner",true)

			this.getJSON(window.apiBase+"client/get?order="+order+"&filters="+filters).then(response => {
				vm.clients = response
				vm.$emit("toggleSpinner",false)
			})
		},
		fetchClients() {
			let vm = this

			let order = JSON.stringify(["company","ASC"]);

			this.getJSON(window.apiBase + "client/get?order="+order).then(function(response){
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
		fetchUsers() {
			let vm = this

			this.getJSON(window.apiBase+"user/get").then(response => {
				vm.users = response
			})
		},
		fetchProjects(filters, order) {
			let vm = this

			vm.$emit("toggleSpinner",true)

			let endpoint = "project/get"

			if (filters || order)
				endpoint += "?"

			if (filters) {
				filters = JSON.stringify(filters)
				endpoint = "project/get?filters="+filters
			}

			if (order) {
				if (filters)
					endpoint += "&"

				order = JSON.stringify(order)
				endpoint += "order="+order
			}

			this.getJSON(window.apiBase + endpoint).then(function(response){
				if (response.status == "offline") {
					//try to pull from localstorage
					if (localStorage.projects) {
						vm.projects = JSON.parse(localStorage.projects)
					}
					vm.$root.isOffline = true
				}
				else {
					vm.projects = response
					localStorage.projects = JSON.stringify(response)
					vm.$root.isOffline = false
				}

				vm.$emit("toggleSpinner",false)
			})
		},
		formatDate(date,format) {
			if (format == "short")
				return moment(date).format("YYYY-MM-DD")
			else if (format == "long")
				return moment(date).format("MMMM Do YYYY")
			else if (format == "short, minutes")
				return moment(date).format("YYYY-MM-DD hh:mm")
			else if (format == "long, minutes")
				return moment(date).format("MMM Do YYYY hh:mm")

		}
	}
}