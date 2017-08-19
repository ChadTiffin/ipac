<template><div>
	<section v-if="!$root.isOffline">
		<router-link class="btn btn-success" to="/templates/sections/edit/new"><i class="fa fa-plus"></i> New Section Template</router-link>

		<table-list 
			:records="templates"
			:fields="fields" 
			has-edit="/templates/sections/edit/" 
			:has-delete="true"
			delete-endpoint="sectionTemplate/delete"
			v-on:modelChange="fetchTemplates">
		</table-list>
	</section>
	<page-offline-alert v-else></page-offline-alert>
</div></template>

<script type="text/javascript">
	import TableList from '../components/TableList'
	import PageOfflineAlert from '../components/PageOfflineAlert'

	export default {
		name: "SectionTemplatesList",
		props: [],
		components: {
			TableList,
			PageOfflineAlert
		},
		data () {
			return {
				fields: [
					{
						key: "heading",
						label: "Section Heading"
					},
					{
						key: "template_description",
						label: "Template Description"
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

				this.getJSON(window.apiBase + "sectionTemplate/get").then(function(response){

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

