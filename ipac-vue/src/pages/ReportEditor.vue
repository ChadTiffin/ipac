<template>
	<section>
		<div class="button-bar">
			<router-link to="/reports"><i class="fa fa-angle-double-left"></i> Back to Reports</router-link>

			<div class="pull-right">
				<button class="btn btn-info" type="button" v-on:click="varHelpVisible = true">
					<i class="fa fa-question-circle"></i> Variable Reference
				</button>

				<button class="btn btn-success" v-on:click="save">
					<i class="fa fa-save"></i> Save
				</button>

				<a class="btn btn-primary" :href="'/reports/view/'+$route.params.id" target="_blank">
					<i class="fa fa-eye"></i> View Report
				</a>
			</div>
			<div style="clear: both;"></div>
		</div>

		<form class="form-horizontal" style="margin-top: 20px;">
			<form-group label="Report Title" col-class="col-lg-2">
				<input type="text" v-model="report.report_title" class="form-control">
			</form-group>

			<form-group label="Report Issue Date" col-class="col-lg-2">
				<date-field v-model="report.date_issued" extra-classes="form-control"></date-field>
			</form-group>

			<div class="row">
				<div class="col-md-4">
					<h3>Report Sections</h3>
					<ul class="list-group">
						<li v-for="section in sections" v-on:click="editSection(section)" class="list-group-item">{{ section.heading }}</li>
					</ul>
				</div>
				<div class="col-md-8">
					
					<div v-show="activeSection == section.id" class="section-editor" v-for="section in sections">
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
		props: [],
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
				},
				activeSection: null,
				varHelpVisible: false
			}
		},
		methods: {
			editSection(section) {
				this.activeSection = section.id
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
			view() {

			},
			fetchReport() {
				let vm = this

				let filter = JSON.stringify([
					['reports.id',this.$route.params.id]
				])

				this.getJSON(window.apiBase+"report/find/"+this.$route.params.id).then(function(response){

					vm.sections = response.sections
					vm.activeSection = response.sections[0].id
					vm.report.date_issued = response.date_issued
					vm.report.report_title = response.report_title

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

</style>

