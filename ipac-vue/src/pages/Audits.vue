<template>
	<section>
		<router-link class="btn btn-success" to="audits/new"><i class="fa fa-plus"></i> New Audit</router-link>

		<search-widget v-model="searchTerms" v-on:submit="fetchAudits"></search-widget>

		<table-list 
			:records="audits" 
			:fields="fields" 
			has-edit="audits/form/" 
			:has-delete="true" 
			v-on:modelChange="fetchAudits" 
			delete-endpoint="filledForm/delete">
			
		</table-list>

	</section>

</template>

<script type="text/javascript">
	import TableList from '../components/TableList'
	import SearchWidget from '../components/SearchWidget'

	export default {
		name: "Audits",
		props: [],
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

				this.getJSON(window.apiBase + "auditForm/get?filters="+filters).then(function(response){
					vm.audits = response

					vm.audits.forEach(function(audit, index){
						vm.$set(audit,"client_company",audit.clients.company)
						vm.$set(audit,"client_location",audit.locations.location_name)
						vm.$set(audit,"form_template_name",audit.form_templates.form_name)
					})

					vm.$emit("toggleSpinner",false)
				})
			},
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.fetchAudits();
		}
	}
</script>

<style type="text/css" scoped>



</style>

