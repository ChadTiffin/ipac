<template>
	<section >
		<div class="button-bar">
			<router-link :to="'/clients/'+currentClientId" class='router-link'><i class="fa fa-angle-double-left"></i> Back to Client</router-link>
		</div>

		<form class="form-horizontal" style="max-width: 600px" v-on:submit.prevent="save">
			<form-group label="Report Title" col-class="col-md-4">
				<input type="text" v-model="report.report_title" class="form-control">
			</form-group>

			<form-group label="Report Issue Date" col-class="col-md-4">
				<date-field v-model="report.date_issued" extra-classes="form-control"></date-field>
			</form-group>

			<form-group label="Client" col-class="col-md-4">
				<select class="form-control" v-model="report.client_id" required>
					<option v-for="client in clients" :value="client.id">{{ client.contact_name }}, {{ client.company }}</option>
				</select>
			</form-group>

			<form-group label="Report Template" col-class="col-md-4">
				<select class="form-control" v-model="report.report_template_id" required>
					<option v-for="template in reportTemplates" :value="template.id">{{ template.template_name }}</option>
				</select>
			</form-group>

			<button class="pull-right btn btn-success"><i class="fa fa-save"></i> Create</button>
		</form>
	</section>

</template>

<script type="text/javascript">
	import FormGroup from '../components/FormGroup'
	import DateField from '../components/DateField'

	export default {
		name: "ReportEditor",
		props: ["clients"],
		components: {
			FormGroup,
			DateField
		},
		data () {
			return {
				sections: [],
				report: {
					report_title: "",
					date_issued: "",
					report_template_id: null,
					client_id: null
				},
				reportTemplates: [],
				activeSection: null,
				currentClientId: localStorage.currentClientId
			}
		},
		methods: {
			editSection(section) {
				this.activeSection = section.id
			},
			save() {

				let vm = this
				let payload = this.report

				this.postData(window.apiBase+"report/save",payload).then(function(response){

					vm.$router.push("/reports/edit/"+response.id)

				});
			},
			fetchReportTemplates() {
				let vm = this

				this.getJSON(window.apiBase+"reportTemplate/get").then(function(response){

					vm.reportTemplates = response

					vm.$emit("toggleSpinner",false)

				})
			}
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.fetchReportTemplates()
		}
	}
</script>

<style type="text/css" scoped>
	.form-horizontal {
		min-height: 300px;
	}
</style>

