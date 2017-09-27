<template><div>
	<h2>Section Templates</h2>
	<table-list 
		:records="templates"
		:fields="fields" 
		has-edit="/templates/sections/edit/" 
		:has-delete="true"
		delete-endpoint="sectionTemplate/delete"
		v-on:modelChange="fetchTemplates">
	</table-list>

</div></template>

<script type="text/javascript">
	import TableList from '../components/TableList'

	export default {
		name: "SectionTemplatesList",
		props: [],
		components: {
			TableList
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

