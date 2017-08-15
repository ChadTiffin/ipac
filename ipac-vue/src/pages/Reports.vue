<template>
	<section>
		<router-link class="btn btn-success" to="reports/new"><i class="fa fa-plus"></i> New Report</router-link>

		<table-list 
			:records="amendedReports" 
			:fields="fields" 
			has-edit="reports/edit/" 
			:has-delete="true" 
			v-on:modelChange="fetchTemplates" 
			:other-buttons="otherButtons"
			delete-endpoint="report/delete">
			
		</table-list>

	</section>

</template>

<script type="text/javascript">
	import TableList from '../components/TableList'

	export default {
		name: "Reports",
		props: [],
		components: {
			TableList,
		},
		data () {
			return {
				fields: [
					{
						key: "report_title",
						label: "Report Title"
					},
					{
						key: "contact_name",
						label: "Client Contact"
					},
					{
						key: "company",
						label: "Client Company"
					},
					{
						key: "date_issued",
						label: "Date Issued"
					},

				],
				reports: [],
				otherButtons: [
					{
						link: "/reports/view/",
						text: "",
						class: "btn-primary",
						icon: "fa-eye"
					}
				]
			}
		},
		computed: {
			amendedReports() {
				let amended = []

				this.reports.forEach(function(report,index) {
					report['contact_name'] = report.clients.contact_name
					report['company'] = report.clients.company

					amended.push(report)
				})

				return amended
			}
		},
		methods: {
			fetchTemplates() {
				let vm = this

				this.getJSON(window.apiBase + "report/get").then(function(response){
					vm.reports = response

					vm.$emit("toggleSpinner",false)
				})
			},
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.fetchTemplates();
		}
	}
</script>

<style type="text/css" scoped>



</style>

