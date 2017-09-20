<template>
	<section>
		<button class="btn btn-success" v-on:click="newAudit"><i class="fa fa-plus"></i> New Audit</button>

		<search-widget v-model="searchTerms" v-on:submit="fetchAudits"></search-widget>

		<table-list 
			:records="audits" 
			:fields="fields" 
			has-edit="/audits/form/" 
			:has-delete="!$root.isOffline" 
			v-on:modelChange="fetchAudits" 
			delete-endpoint="auditForm/delete">
			
		</table-list>

		<div class="alert alert-info" v-if="$root.isOffline">
			<i class="fa fa-warning"></i>
			Since you are offline, only the audits that were recently stored locally will be accessible. In order to access all your audits you need to connect to the internet!
		</div>

	</section>

</template>

<script type="text/javascript">
	import TableList from '../components/TableList'
	import SearchWidget from '../components/SearchWidget'
	import bus from '../bus.js'

	export default {
		name: "Audits",
		components: {
			TableList,
			SearchWidget
		},
		data () {
			return {
				fields: [
					{
						key: "audit_date",
						label: "Audit Date"
					},
					{
						key: "client_company",
						label: "Client"
					},
					{
						key: "client_location",
						label: "Location"
					},
					{
						key: "form_template_name",
						label: "Form"
					},

				],
				audits: [],
				otherButtons: [
					{
						link: "/reports/view/",
						text: "",
						class: "btn-primary",
						icon: "fa-eye"
					}
				],
				searchTerms: ""
			}
		},
		methods: {
			newAudit() {
				this.$emit("newAudit")
			},
			fetchAudits() {
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

					//get date 14 days ago
					var today = new Date()
					var priorDate = new Date().setDate(today.getDate()-14)

					priorDate = new Date(priorDate)
					var date_string = priorDate.getFullYear()+"-"+(priorDate.getMonth()+1)+"-"+priorDate.getDate()

					filters = JSON.stringify([
						["audits.updated_at >=",date_string]
					])
				}

				let order = JSON.stringify(["audit_date","DESC"])

				this.getJSON(window.apiBase + "auditForm/get?filters="+filters+"&order="+order).then(function(response){

					if ("status" in response && response.status == "offline") {
						if (localStorage.offlineAudits) {
							let offlineAudits = JSON.parse(localStorage.offlineAudits)

							//iterate audits and manually join client, location, and form templates

							let locations = JSON.parse(localStorage.locations)
							let clients = JSON.parse(localStorage.clients)
							let auditTemplates = JSON.parse(localStorage.auditTemplates)

							offlineAudits.forEach(function(audit, index){
								locations.forEach(function(location,index) {
									if (location.id == audit.location_id) 
										audit.client_location = location.location_name
								})

								clients.forEach(function(client, index){
									if (client.id == audit.client_id)
										audit.client_company = client.company
								})

								auditTemplates.forEach(function(template, index){
									if (template.id == audit.form_template_id)
										audit.form_template_name = template.form_name
								})
							})

							vm.audits = offlineAudits
						}
					}
					else {
						vm.audits = response

						vm.audits.forEach(function(audit, index){
							vm.$set(audit,"client_company",audit.clients.company)
							vm.$set(audit,"client_location",audit.locations.location_name)
							vm.$set(audit,"form_template_name",audit.form_templates.form_name)
						})
					}
					
					vm.$emit("toggleSpinner",false)
				})
			},
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.fetchAudits();

			let vm = this
			bus.$on("auditsChanged",function(){
				vm.fetchAudits()
			})
		}
	}
</script>

<style type="text/css" scoped>



</style>

