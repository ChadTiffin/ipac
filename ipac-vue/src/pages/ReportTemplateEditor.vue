<template>
	<section>
		<router-link to="/templates/reports"><i class="fa fa-angle-double-left"></i> Back to Report Templates</router-link>

		<form class="form-horizontal" v-on:submit.prevent="save">

			<div class="field-set">
				<button class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save</button>
				<div style="clear: both;"></div>
			</div>
			

			<div class="field-set">
				<form-group label="Report Name" col-class="col-md-2">
					<input type="text" class="form-control" v-model="report.template_name">
				</form-group>
			</div>
			<div class="field-set">
				<form-group label="Preface Page Text" col-class="col-md-2">
					<rich-text v-model="report.preface_text" id="preface_text"></rich-text>
				</form-group>
			</div>

			<div class="field-set">
				<p>Drag over section templates from right to left to include them in this report template</p>
				<div class="row">
					<div class="col-md-6">
						<h2>Included in Report Template</h2>

						
						<ul class="list-group">
							<draggable v-model="includedSections" :options="{group:'sections'}"  style="min-height: 200px">
							
								<li class="list-group-item" v-for="section in includedSections">
									{{ section.heading }}
									<div v-if="section.template_description != ''">
										<small>({{ section.template_description }})</small>
									</div>
								</li>

							</draggable>
						</ul>
						
					</div>
					<div class="col-md-6">
						<h2>Available Sections</h2>
						<ul class="list-group">
							<draggable v-model="availableSections" :options="{group:'sections'}" style="min-height: 200px">

								<li class="list-group-item" v-for="section in availableSections">
									{{ section.heading }}
									<div v-if="section.template_description != ''">
										<small>({{ section.template_description }})</small>
									</div>
								</li>

							</draggable>
						</ul>
					</div>
				</div>
			</div>

			<div class="field-set">
				<button class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save</button>
			
				<div style="clear: both;"></div>
			</div>
		</form>
	</section>

</template>

<script type="text/javascript">
	import RichText from '../components/RichText'
	import FormGroup from '../components/FormGroup'
	import draggable from 'vuedraggable'

	export default {
		name: "ReportTemplateEditor",
		props: [],
		components: {
			FormGroup,
			RichText,
			draggable
		},
		data () {
			return {
				report: {
					id: "new",
					template_name: "",
					preface_text: ""
				},
				includedSections: [],
				allSections: [],
				availableSections: []

			}
		},
		watch: {
			includedSections() {
				this.computeAvailableSections()
			}
		},
		methods: {
			autoSave() {
			},
			computeAvailableSections() {
				let vm = this
				let avail = []

				this.allSections.forEach(function(section, index) {
					let found = false
					vm.includedSections.forEach(function(includedSection, index) {
						if (includedSection.id == section.id) 
							found = true
					})

					if (!found) 
						avail.push(section)
					
				});

				this.availableSections = avail;
			},
			fetchTemplate() {
				
				let id = this.$route.params.id

				let vm = this

				if (id != "new") {
					

					this.getJSON(window.apiBase+"reportTemplate/find/"+id).then(function(response){
						vm.report.template_name = response.template_name
						vm.includedSections = response.section_templates
						vm.report.preface_text = response.preface_text
						vm.report.id = response.id

						vm.computeAvailableSections()

						vm.$emit("toggleSpinner",false)
					});
				}
				else
					vm.$emit("toggleSpinner",false)
			},
			fetchAllSectionTemplates() {
				let vm = this

				this.getJSON(window.apiBase+"sectionTemplate/get").then(function(response){
					vm.allSections = response

					vm.computeAvailableSections()

				})
			},
			save() {

				let payload = {
					id: this.report.id,
					template_name: this.report.template_name,
					preface_text: this.report.preface_text,
					includedSections: JSON.stringify(this.includedSections)
				}

				let vm = this

				this.postData(window.apiBase+ "reportTemplate/save",payload).then(function(response){

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
			this.fetchAllSectionTemplates();
		}
	}
</script>

<style type="text/css" scoped>

.list-group {
	border: 1px solid #e0e0e0;
	padding: 2px;
}

</style>

