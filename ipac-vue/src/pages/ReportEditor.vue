<template>
	<section>
		<div class="button-bar">
			<router-link :to="'/clients/'+currentClientId" class='router-link'><i class="fa fa-angle-double-left"></i> Back to Client</router-link>

			<div class="pull-right">
				<button class="btn btn-info" type="button" v-on:click="varHelpVisible = true">
					<i class="fa fa-question-circle"></i> Variable Reference
				</button>

				<a class="btn btn-primary" :href="'/reports/view/'+$route.params.id" target="_blank">
					<i class="fa fa-eye"></i> View Report
				</a>

				<a class="btn btn-primary" :href="pdfUrl" target="_blank">
					<i class="fa fa-download"></i> Report PDF
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

					<form-group label="Location" col-class="col-md-4">
						<select class="form-control" v-model="report.location_id" v-on:change="fetchAudits">
							<option v-for="location in locations" :value="location.id">{{location.location_name}}</option>
						</select>
					</form-group>
				</div>
				<div class="col-md-6">
					<form-group label="Audits to Import From" col-class="col-md-4">
						<p v-if="auditsLoading" class="form-control-static"><i class="fa fa-spin fa-spinner"></i></p>
						<select v-else multiple class="form-control" v-model="includedAudits">
							<option v-for="audit in audits" :value="audit.id">{{ audit.audit_date}} {{ audit.form_templates.form_name }}</option>
						</select>
					</form-group>
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<h3>Report Sections</h3>
					<ul class="nav nav-pills nav-stacked report-sections">
						<li v-for="section in sections" :class="{active: activeSection == section.id}"><a href="#" v-on:click="activeSection = section.id">{{ section.heading }}</a></li>
					</ul>
				</div>
				<div class="col-md-8">

					
					<div v-show="activeSection == section.id" class="section-editor" v-for="section in sections">

						<button type="button" class="btn btn-primary pull-right" :class="{ disabled : includedAudits.length == 0 }" v-on:click="importAuditData(section)"><i class="fa fa-download"></i> Import Audit Data Into Findings</button>

						<h3><small>Findings:</small> {{ section.heading }}</h3>

						<rich-text :id="'editor-'+section.id" v-model="section.findings"></rich-text>
					</div>
					
				</div>
			</div>
		</form>

		<variable-help v-if="varHelpVisible" v-on:close="varHelpVisible = false" :modal-visible="varHelpVisible"></variable-help>
	</section>

</template>

<script type="text/javascript">
	import RichText from '../components/RichText'
	import FormGroup from '../components/FormGroup'
	import DateField from '../components/DateField'
	import draggable from 'vuedraggable'
	import VariableHelp from '../components/VariableHelp'

	export default {
		name: "ReportEditor",
		components: {
			FormGroup,
			RichText,
			DateField,
			draggable,
			VariableHelp
		},
		data () {
			return {
				sections: [],
				report: {
					report_title: "",
					date_issued: "",
					location_id: null
				},
				includedAudits: [],
				reportMeta: {},
				locations: [],
				audits: [],
				auditsLoading: true,
				activeSection: null,
				varHelpVisible: false,
				currentClientId: localStorage.currentClientId,
				pdfUrl: window.apiBase+"report/pdf/"+this.$route.params.id+"?key="+localStorage.apiKey
			}
		},
		methods: {
			importAuditData(section) {

				let vm = this
				let findings_html = "<ul>";
				let findings_images = "";
				this.audits.forEach(function(audit,index){

					if (vm.includedAudits.indexOf(audit.id) >= 0) {

						if (audit.form_template_id == section.findings_form_template_id) {
							//this is the template to pull from
							let fields = JSON.parse(audit.form_values)

							fields.forEach(function(field_section, index) {

								if (field_section.heading == section.findings_section_name) {
									//we've found it, pull in all the photos and negative answer notes

									if ("fields" in field_section) {
										console.log("searching main fields...")
										field_section.fields.forEach(function(field, index){
											
											if (field.type == 'images' || field.type =='image') {
												console.log("images found")
												if (field.value) {
													field.value.forEach(function(image,index){
														findings_images += "<img style='width:320px' src='"+window.apiBase+"image/image/"+image+"'>"
													})
												}
											}
											else if (field.type == 'yes/no') {
												if (field.value == "no" && field.notes) {
													findings_html += "<li><strong>"+field.question+":</strong> "+field.notes+"</li>"
												}
												else if (field.value == "no") {
													findings_html += "<li><strong>"+field.question+":</strong> NO</li>"
												}
											}
										})
									}
									if ("subSections" in field_section) {
										console.log("searching subsection fields...")
										field_section.subSections.forEach(function(subSection, index) {
											if ("fields" in subSection) {
												subSection.fields.forEach(function(field, index) {

													if (field.type == 'images' || field.type =='image') {
														console.log("images found")
														if (field.value) {
															field.value.forEach(function(image,index){
																findings_images += "<img style='width:320px' src='"+window.apiBase+"image/image/"+image+"'>"
															})
														}
													}
													else if (field.type == 'yes/no') {
														if (field.value == "no" && field.notes) {
															findings_html += "<li><strong>"+field.question+":</strong> "+field.notes+"</li>"
														}
														else if (field.value == "no") {
															findings_html += "<li><strong>NO</strong> "+field.question+"</li>"
														}
													}
												})
											}
										})
									}
								}
							})
						}
					}
				})
				findings_html += "</ul>"

				if (findings_html == "<ul></ul>") {
					findings_html = "<p>No violations found</p>"
				}

				let editor = tinymce.get("editor-"+section.id)

				editor.insertContent(findings_images+findings_html)
			},
			save() {

				let sections_payload = [];
				let vm = this

				this.sections.forEach(function(section, index) {
					sections_payload.push({
						report_id: vm.$route.params.id,
						section_template_id: section.section_template_id,
						body_text: section.findings
					})
				})

				let payload = {
					sections: sections_payload,
					report: this.report,
					id: this.$route.params.id
				}

				this.postData(window.apiBase+"report/save-report",payload).then(function(response){

					vm.$emit("updateAlert",{
						visible: true,
						class: "alert-success",
						msg: "Report saved"
					})

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
						if (section.has_findings == 1)
							vm.sections.push(section)
					})

					vm.report.date_issued = response.date_issued
					vm.report.report_title = response.report_title
					vm.report.location_id = response.location_id

					if (response.sections.length > 0)
						vm.activeSection = response.sections[0].id

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

