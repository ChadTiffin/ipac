<template>
	<section>
		<router-link class="btn btn-success" to="/templates/sections/edit/new"><i class="fa fa-plus"></i> New Section Template</router-link>

		<table-list :records="templates" :fields="fields" has-edit="/templates/sections/edit/" has-delete="true" v-on:edit="editTemplate"></table-list>
	</section>

</template>

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
				templates: []
			}
		},
		methods: {
			fetchTemplates() {
				let vm = this

				this.getJSON(window.apiBase + "sectionTemplate/get").then(function(response){
					vm.templates = response

					vm.$emit("toggleSpinner",false)
				})
			},
			editTemplate(template) {
				window.location = "/edit-section-template/"+template.id
			}
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.fetchTemplates();
		}
	}
</script>

<style type="text/css" scoped>



</style>

