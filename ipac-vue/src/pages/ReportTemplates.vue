<template>
	<section>
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

</template>

<script type="text/javascript">
	import TableList from '../components/TableList'

	export default {
		name: "ReportTemplatesList",
		props: [],
		components: {
			TableList,
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
					vm.templates = response

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

