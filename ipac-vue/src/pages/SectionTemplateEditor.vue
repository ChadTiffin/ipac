<template>
	<section>
		
		<form class="form-horizontal" v-on:submit.prevent="save">

			<div class="button-bar">
			
				<router-link to="/templates/sections"><i class="fa fa-angle-double-left"></i> Back to Section Templates</router-link>

				<div class="pull-right">
					<button class="btn btn-info" type="button" v-on:click="varHelpVisible = true">
						<i class="fa fa-question-circle"></i> Variable Reference
					</button>

					<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
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
				<rich-text id="intro-text" v-model="section.description_text" v-on:input="logChange"></rich-text>
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

			<form-group v-if="section.has_findings == '1'" label="" col-class="col-md-2">
				Findings are unique to each report, so this sub-section will not be templated (for now). You can edit the "Findings" when you create a report.
			</form-group>
		
			<button class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save</button>
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
				varHelpVisible: false
			}
		},
		methods: {
			logChange() {
				//console.log(this.section.description_text)
			},
			fetchTemplate() {
				
				let id = this.$route.params.id

				let vm = this

				if (id != "new") {
					

					this.getJSON(window.apiBase+"sectionTemplate/find/"+id).then(function(response){
						vm.section = response
						for (var key in response) {
							vm.$set(vm.section,key,response[key])
						}

						vm.$emit("toggleSpinner",false)
					});
				}
				else 
					vm.$emit("toggleSpinner",false)
			},
			save() {
				let payload = this.section

				delete payload.updated_at

				let vm = this
				this.postData(window.apiBase+ "sectionTemplate/save",payload).then(function(response){
					if (response.status == "success") {
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
		}
	}
</script>

<style type="text/css" scoped>



</style>

