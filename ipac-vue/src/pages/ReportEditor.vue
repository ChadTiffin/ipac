<template>
	<section>
		<div class="button-bar">
			<router-link :to="'/clients/'+currentClientId+'/reports'" class='router-link'><i class="fa fa-angle-double-left"></i> Back to Client</router-link>

			<div class="pull-right">
				<button class="btn btn-info" type="button" v-on:click="varHelpVisible = true">
					<i class="fa fa-question-circle"></i> Variable Reference
				</button>

				<!--<a class="btn btn-primary" :href="'/reports/view/'+$route.params.id" target="_blank">
					<i class="fa fa-eye"></i> View Report
				</a>-->

				<a class="btn btn-primary" :href="pdfUrl" target="_blank">
					<i class="fa fa-pdf-o"></i> Report PDF
				</a>

				<a class="btn btn-primary" :href="pdfCoverUrl" target="_blank">
					<i class="fa fa-pdf-o"></i> Report PDF - Cover
				</a>

				<a class="btn btn-primary" :href="docUrl" target="_blank">
					<i class="fa fa-file-word-o"></i> Report to Word
				</a>

				<button class="btn btn-success" v-on:click="save">
					<i class="fa fa-save"></i> Save
				</button>
			</div>
			<div style="clear: both;"></div>
		</div>

		<form class="form-horizontal" style="margin-top: 20px;">
			<div class="row">
				<div class="col-md-6">
					<form-group label="Report Title" col-class="col-md-4">
						<input type="text" v-model="report.report_title" class="form-control">
					</form-group>

					<form-group label="Report Issue Date" col-class="col-md-4">
						<date-field v-model="report.date_issued" extra-classes="form-control"></date-field>
					</form-group>

					
				</div>
				<div class="col-md-6">
					<form-group label="Location" col-class="col-md-4">
						<select class="form-control" v-model="report.location_id" v-on:change="fetchAudits">
							<option v-for="location in locations" :value="location.id">{{location.location_name}}</option>
						</select>
					</form-group>
					<!--<form-group label="Report Images" col-class="col-md-4">
						<image-upload-field :api-base="apiBase" :multi="true" 
							v-on:imageListChanged="saveImageFieldChange" 
							:images="report.extra_images" ></image-upload-field>
					</form-group>-->
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<h2>Report Sections</h2>
					<ul class="nav nav-pills nav-stacked report-sections">
						<li v-for="section in sections" :class="{active: activeSection == section.id}"><a href="#" v-on:click="activeSection = section.id">{{ section.heading }}</a></li>
					</ul>
				</div>
				<div class="col-md-8">
					
					<div v-show="activeSection == section.id" class="section-editor" v-for="section in sections">

						<button type="button" class="btn btn-primary pull-right" v-on:click="importPrompt(section)"><i class="fa fa-download"></i> Import Audit Data Into Findings</button>

						<h2><small>Findings:</small> {{ section.heading }}</h2>

						<image-upload-field :api-base="apiBase" :multi="true" 
							v-on:imageListChanged="saveImageFieldChange($event, section)" 
							upload-msg="Upload section images here"
							:images="section.images" 
							upload-type="audit-image">
						</image-upload-field>

						<rich-text :key="section.id" :id="'editor-'+section.id" v-on:input="editorChanged" v-model="section.findings"></rich-text>
					</div>
					
				</div>
			</div>
		</form>

		<variable-help v-if="varHelpVisible" v-on:close="varHelpVisible = false" :modal-visible="varHelpVisible"></variable-help>

		<modal-dialog
			v-if="importModal.visible"
			:modal-visible="importModal.visible"
			title="Audit Import"
			confirm-button-text="Import"
			button-class="btn-primary"
			v-on:confirm="importAuditData(importModal.section)"
			v-on:closeModal="importModal.visible = false"
			>
			<form-group label="Audits to Import From" col-class="col-md-4">
				<p v-if="auditsLoading" class="form-control-static"><i class="fa fa-spin fa-spinner"></i></p>
				<select v-else multiple class="form-control" v-model="includedAudits">
					<option v-for="audit in audits" :value="audit.id">{{ audit.audit_date}} {{ audit.form_templates.form_name }}</option>
				</select>
			</form-group>

			<div class="col-md-offset-4">
				<label>
					<input type="checkbox" v-model="includePositiveFindings">
					Import Positive as well as negative findings
				</label>
			</div>

		</modal-dialog>
	</section>

</template>

<script type="text/javascript">
	import RichText from '../components/RichText'
	import FormGroup from '../components/FormGroup'
	import DateField from '../components/DateField'
	import draggable from 'vuedraggable'
	import VariableHelp from '../components/VariableHelp'
	import ImageUploadField from '../components/ImageUploadField'
	import ModalDialog from '../components/ModalDialog'

	export default {
		name: "ReportEditor",
		components: {
			FormGroup,
			RichText,
			DateField,
			draggable,
			VariableHelp,
			ImageUploadField,
			ModalDialog
		},
		data () {
			return {
				apiBase: window.apiBase,
				sections: [],
				report: {
					report_title: "",
					date_issued: "",
					location_id: null,
					extra_images: []
				},
				importModal: {
					visible: false,
					section: null
				},
				includedAudits: [],
				includePositiveFindings: false,
				reportMeta: {},
				locations: [],
				audits: [],
				auditsLoading: true,
				activeSection: null,
				varHelpVisible: false,
				currentClientId: localStorage.currentClientId,
				pdfUrl: window.apiBase+"report/pdf/"+this.$route.params.id+"?key="+localStorage.apiKey,
				pdfCoverUrl: window.apiBase+"report/pdf/"+this.$route.params.id+"/cover?key="+localStorage.apiKey,
				docUrl: window.apiBase+"report/text/"+this.$route.params.id+"/word?key="+localStorage.apiKey,
				typingTimer: null
			}
		},
		methods: {
			editorChanged() {
				let vm = this

				vm.$emit("updateAlert",{
					visible: true,
					class: "alert-info",
					msg: "Preparing to save...",
					icon: "fa-spin fa-spinner"
				})

				clearTimeout(this.typingTimer);

				this.typingTimer = setTimeout(function(){

					vm.$emit("updateAlert",{
						visible: true,
						class: "alert-info",
						msg: "Saving...",
						icon: "fa-save"
					})

					vm.save()

				},1000)
			},
			saveImageFieldChange($eventParams, section) {

				if ($eventParams.type == "addition")
					section.images.push($eventParams.response.filename)
				else if ($eventParams.type == "deletion") {
					section.images.splice(section.images.indexOf($eventParams.removed_image))
				}

				this.save()

			},
			importPrompt(section) {
				this.importModal = {
					visible: true,
					section: section
				}
			},
			//modifies output in place 
			outputQuestion(section,field,findings_html, findings_images) {
				console.log(field)

				let vm = this

				let notes = "";
				if (typeof field.notes != "undefined")
					notes = field.notes

				if (field.type == 'images' || field.type =='image') {
					//console.log("images found")
					if (field.value) {

						if (!section.images)
							section.images = field.value
						else
							section.images = section.images.concat(field.value)

						/*field.value.forEach(function(image,index){
							findings_images += "<img style='width:320px' src='"+window.apiBase+"image/serve/public/"+image+"'>"
						})*/
					}
					if (field.hasNotes && field.notes != "") 
						findings_images += "<p>"+notes+"</p>"
					
				}
				else if (field.type == 'yes/no') {

					if (typeof field.value != 'undefined') {

						//add question
						findings_html += "<li><em>"+field.question+"</em>: <ul><li><strong>Observation: </strong>"

						if (field.notes) 
							findings_html += notes
						
						else if (field.value == "no" || vm.includePositiveFindings) {

							if (field.value)
								findings_html += field.value.toUpperCase()
						}

						findings_html += "</li>"

						if ("recommendation" in field) 
							findings_html += "<li><strong>Recommendation: </strong>"+field.recommendation+"</li>"
						

						findings_html += "<ul></li>"
					}

				}
				else if (field.type == "opportunityCounter") {

					let percentage = Math.round((field.value[0] / (field.value[0] + field.value[1])) * 100)

					findings_html += "<li><em>"+field.question+":</em> "+percentage + "%"+"</li>"
				}
				else if (field.type == 'textarea') {
					findings_html += "<li><em>"+field.question+"</em>: <ul><li>"+field.value+"</li></ul></li>"
				}

				if ("observations" in field || "recommendations" in field)
					findings_html += "<li><ul>"

				if ("observations" in field)
					findings_html += "<li><strong>Observations</strong>: "+field.observations+"</li>"

				if ("recommendations" in field)
					findings_html += "<li><strong>Recommendations</strong>: "+field.recommendations+"</li>"

				if ("observations" in field || "recommendations" in field)
					findings_html += "</ul></li>"

				return [findings_html,findings_images]
			},
			importAuditData(section) {

				this.importModal.visible = false

				let vm = this
				let findings_html = "";
				let findings_images = "";

				let findings_section_names = JSON.parse(section.findings_section_names)

				//find audit that was selected
				let audit_date = null
				vm.audits.forEach(audit => {
					if (vm.includedAudits[0] == audit.id)
						audit_date = audit.audit_date
				})

				//save audit date to report
				let payload = {
					id: this.$route.params.id, 
					audit_date: audit_date
				}
				this.postData(window.apiBase+"report/save",payload)

				this.audits.forEach(function(audit,index){

					if (vm.includedAudits.indexOf(audit.id) >= 0) {

						if (audit.form_template_id == section.findings_form_template_id) {
							//this is the template to pull from
							let fields = []
							if (audit.form_values != "")
								fields = JSON.parse(audit.form_values)

							fields.forEach(function(field_section, index) {

								if (findings_section_names.indexOf(field_section.heading) >= 0) { //search for field section heading in array of titles of bound sections
									//we've found it, pull in all the photos and answer notes

									findings_html += "<p><strong>" + field_section.heading + "</strong></p>"
									findings_html += "<ul>"

									if ("fields" in field_section) {
										//console.log("searching main fields...")
										field_section.fields.forEach(function(field, index){

											let result = vm.outputQuestion(section,field,findings_html, findings_images)

											findings_html = result[0]
											findings_images = result[1]

										})
									}
									if ("subSections" in field_section) {
										//console.log("searching subsection fields...")
										field_section.subSections.forEach(function(subSection, index) {
											if ("fields" in subSection) {
												subSection.fields.forEach(function(field, index) {

													let result = vm.outputQuestion(subSection,field,findings_html, findings_images)

													findings_html = result[0]
													findings_images = result[1]

												})

											}
										})
									}

									findings_html += "</ul>";
								}
							})
						}
					}
				})

				if (findings_html == "") {
					if (vm.includePositiveFindings) 
						findings_html = "<p>No import data found</p>"
					else
						findings_html = "<p>No negative findings found on Audit</p>"
				}

				let editor = tinymce.get("editor-"+section.id)

				editor.insertContent(findings_html)
			},
			save() {

				let sections_payload = [];
				let vm = this

				this.sections.forEach(function(section, index) {
					sections_payload.push({
						report_id: vm.$route.params.id,
						section_template_id: section.section_template_id,
						body_text: section.findings,
						images: JSON.stringify(section.images)
					})
				})

				let payload = {
					sections: sections_payload,
					report: this.report,
					id: this.$route.params.id
				}

				this.postData(window.apiBase+"report/save-report",payload).then(function(response){

					if (response.status == "success") {
						vm.$emit("updateAlert",{
							visible: true,
							class: "alert-success",
							msg: "Report saved",
							icon: "fa-save"
						})
					}
					else {
						vm.$emit("updateAlert",{
							visible: true,
							class: "alert-danger",
							msg: "Oops! Something happened. The Report did not save.",
							icon: "fa-warning"
						})
					}

				});
			},
			fetchLocations() {
				let vm = this

				let filters = JSON.stringify([
					["client_id",this.reportMeta.client_id]
				])

				let order = JSON.stringify(["location_name","ASC"]);

				this.getJSON(window.apiBase + "location/get?filters="+filters+"&order="+order).then(function(response){
					vm.locations = response
				})
			},
			fetchAudits() {
				let vm = this

				this.auditsLoading = true;
				let filters = JSON.stringify([
					["audits.client_id",this.reportMeta.client_id],
					["audits.location_id",this.report.location_id]
				])

				let order = JSON.stringify(["audit_date","DESC"]);

				this.getJSON(window.apiBase + "auditForm/get?filters="+filters+"&order="+order).then(function(response){
					vm.audits = response
					vm.auditsLoading = false;
				})
			},
			fetchReport() {
				let vm = this

				let filter = JSON.stringify([
					['reports.id',this.$route.params.id]
				])

				this.getJSON(window.apiBase+"report/find/"+this.$route.params.id).then(function(response){

					vm.sections = response.sections

					vm.sections = []
					response.sections.forEach(function(section, index){
						if (section.has_findings == 1) {

							if (!vm.activeSection)
								vm.activeSection = section.id

							if (section.images)
								section.images = JSON.parse(section.images)
							else
								section.images = []

							vm.sections.push(section)
						}
					})

					vm.report.date_issued = response.date_issued
					vm.report.report_title = response.report_title
					vm.report.location_id = response.location_id

					if (response.extra_images)
						vm.report.extra_images = JSON.parse(response.extra_images)
					else
						vm.report.extra_images = []

					vm.reportMeta = response

					vm.fetchLocations()
					vm.fetchAudits()

					vm.$emit("toggleSpinner",false)

				})
			}
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.fetchReport()

		}
	}
</script>

<style type="text/css" scoped>
	.report-sections .list-group-item:hover {
		cursor: pointer;
		background-color: blue;
	}
</style>

