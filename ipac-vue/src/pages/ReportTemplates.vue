<template><div>
	<section v-if="!$root.isOffline">
		<router-link to="/templates/reports/edit/new" class="btn btn-success"><i class="fa fa-plus"></i> New Report Template</router-link>

		<table-list 
			:records="templates" 
			:fields="fields" 
			has-edit="/templates/reports/edit/" 
			delete-endpoint="reportTemplate/delete"
			:has-delete="true"
			v-on:modelChange="fetchTemplates">
		</table-list>
	</section>
	<page-offline-alert v-else></page-offline-alert>

</div></template>

<script type="text/javascript">
	import TableList from '../components/TableList'
	import PageOfflineAlert from '../components/PageOfflineAlert'

	export default {
		name: "ReportTemplatesList",
		props: [],
		components: {
			TableList,
			PageOfflineAlert
		},
		data () {
			return {
				fields: [
					{
						key: "template_name",
						label: "Template Name"
					},
					{
						key: "updated_at",
						label: "Last Updated"
					}
				],
				templates: [],
			}
		},
		methods: {
			fetchTemplates() {
				let vm = this

				this.getJSON(window.apiBase + "reportTemplate/get").then(function(response){

					if ("status" in response && response.status == "offline") {

					}
					else {
						vm.templates = response
					}

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

