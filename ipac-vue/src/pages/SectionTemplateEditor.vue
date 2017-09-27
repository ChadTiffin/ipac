<template>
	<section>
		
		<form class="form-horizontal" v-on:submit.prevent="save">

			<div class="button-bar">
			
				<router-link to="/templates/sections" class="router-link"><i class="fa fa-angle-double-left"></i> Back to Section Templates</router-link>

				<div class="pull-right">
					<button class="btn btn-info" type="button" v-on:click="varHelpVisible = true">
						<i class="fa fa-question-circle"></i> Variable Reference
					</button>

					<button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
				</div>

				<div style="clear: both;"></div>
			</div>
			
			<form-group label="Section Title" col-class="col-md-2">
				<input type="text" class="form-control" v-model="section.heading">
			</form-group>

			<form-group label="Template Description" col-class="col-md-2">
				<input type="text" class="form-control" v-model="section.template_description">
				<span class="help-block">(Template description will not appear in report)</span>
			</form-group>

			<hr>
		
			<form-group label="Introduction Template" col-class="col-md-2">
				<p class="help-block">This text will appear in every report that contains this section</p>
				<rich-text id="intro-text" v-model="section.description_text" v-on:input="richTextChange"></rich-text>
			</form-group>

			<hr>
		
			<form-group label="" col-class="col-md-2">
				<label>
					<input type="checkbox" v-model="section.has_guidelines" true-value="1" false-value="0">
					This section has a "Guidelines" Sub-section
				</label>
			</form-group>

			<form-group v-show="section.has_guidelines == '1'" label="Guidelines Template" col-class="col-md-2">
				<p class="help-block">This text will appear in every report that contains this section</p>
				<rich-text id="guidelines-text" v-model="section.guidelines_text"></rich-text>
			</form-group>

			<hr>
		
			<form-group label="" col-class="col-md-2">
				<label>
					<input type="checkbox" v-model="section.has_findings" true-value="1" false-value="0">
					This section has a "Findings" Sub-section
				</label>
			</form-group>

			<div v-if="section.has_findings == '1'">
				<form-group label="" col-class="col-md-2">
					<label>
						<input type="checkbox" v-model="section.pull_findings_from_section" true-value="1" false-value="0">
						Pull Findings from an audit form section
					</label>
				</form-group>

				<div v-if="section.pull_findings_from_section == '1'">

					<form-group label="Form Template" col-class="col-md-2">
						<select class="form-control" v-model="section.findings_form_template_id">
							<option v-for="template in formTemplates" :value="template.id">{{ template.form_name }}</option>
						</select>
					</form-group>

					<form-group label="Form Section" col-class="col-md-2">
						<select class="form-control" v-model="findingsSectionNames" multiple>
							<option v-for="templateSection in selectedFormTemplateSections">{{ templateSection }}</option>
						</select>
					</form-group>
				</div>
				<div v-else class="col-md-offset-2 col-md-10">
					Findings will be typed out manually
				</div>

			</div>
		
			<button class="btn btn-success pull-right"><i class="fa fa-save"></i> Save</button>
			<div style="clear: both;"></div>

		</form>

		<variable-help v-if="varHelpVisible" v-on:close="varHelpVisible = false" :modal-visible="varHelpVisible"></variable-help>
	</section>

</template>

<script type="text/javascript">
	import RichText from '../components/RichText'
	import FormGroup from '../components/FormGroup'
	import VariableHelp from '../components/VariableHelp'

	export default {
		name: "SectionTemplateEditor",
		props: [],
		components: {
			FormGroup,
			RichText,
			VariableHelp
		},
		data () {
			return {
				section: {},
				findingsSectionNames: [],
				formTemplates: [],
				varHelpVisible: false
			}
		},
		computed: {
			selectedFormTemplateSections() {
				let sections = [];

				let vm = this
				this.formTemplates.forEach(function(form, index){
					let template = JSON.parse(form.fields);

					if (form.id == vm.section.findings_form_template_id) {
						template.forEach(function(section, index){
							sections.push(section.heading)
						})
					}
				})

				return sections;
			}
		},
		methods: {
			richTextChange () {},
			fetchTemplate() {
				
				let id = this.$route.params.id

				let vm = this

				if (id != "new") {
					
					this.getJSON(window.apiBase+"sectionTemplate/find/"+id).then(function(response){

						let findingsSectionNames = JSON.parse(response.findings_section_names)


						if (typeof findingsSectionNames == "string")
							vm.findingsSectionNames = []
						else
							vm.findingsSectionNames = findingsSectionNames

						vm.section = response

						for (var key in response) {

							if (!(key in vm.section)) {

								vm.$set(vm.section,key,response[key])
							}

						}

					}); 
				}

				vm.$emit("toggleSpinner",false)
			},
			fetchFormTemplates() {
				let vm = this

				this.getJSON(window.apiBase + "auditFormTemplate/get").then(function(response){
					vm.formTemplates = response
				})
			},
			save() {

				let payload = this.section

				delete payload.updated_at

				payload.findings_section_names = JSON.stringify(this.findingsSectionNames)

				let vm = this

				this.postData(window.apiBase+ "sectionTemplate/save",payload).then(function(response){

					if (response.status == "success") {

						vm.$router.replace("/templates/sections/edit/"+response.id)

						vm.section.id = response.id

						vm.$emit("updateAlert",{
							visible: true,
							class: "alert-success",
							msg: "Template saved"
						})
					}
				})
			}
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.fetchTemplate();
			this.fetchFormTemplates();
		}
	}
</script>

<style type="text/css" scoped>



</style>

