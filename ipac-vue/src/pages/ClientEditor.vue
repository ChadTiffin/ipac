<template>
	<div>

		<section>
			<div class="button-bar">
				<router-link to="/clients" class='router-link'><i class="fa fa-angle-double-left"></i> Back to Clients</router-link>
			</div>

			<h2>Client Details</h2>

			<form class="row" v-on:submit.prevent="save">

				<div class="col-md-6">
					<div class="form-horizontal">
						<form-group label="Company" col-class="col-md-3">
							<input type="text" v-model='client.company' class="form-control">
						</form-group>

						<form-group label="Contact Name" col-class="col-md-3">
							<input type="text" v-model='client.contact_name' class="form-control">
						</form-group>

						<form-group label="Primary Phone" col-class="col-md-3">
							<input type="tel" v-model='client.primary_phone' class="form-control">
						</form-group>

						<form-group label="Email" col-class="col-md-3">
							<input type="text" v-model='client.email' class="form-control">
						</form-group>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-horizontal">
						<form-group label="Address" col-class="col-md-3">
							<input type="text" v-model='client.address' class="form-control">
						</form-group>

						<form-group label="City" col-class="col-md-3">
							<input type="text" v-model='client.city' class="form-control">
						</form-group>

						<form-group label="Province" col-class="col-md-3">
							<input type="text" v-model='client.province' class="form-control">
						</form-group>

						<form-group label="Postal Code" col-class="col-md-3">
							<input type="text" v-model='client.postal_code' class="form-control">
						</form-group>

						<button class="pull-right btn btn-success"><i class="fa fa-save"></i> Save</button>
					</div>
				</div>

			</form>
			<div class="alert" :class="alert.class" v-if="alert.visible">
				{{ clientDialog.alert.msg }}
				<p v-if="alert.hasErrors" v-for="error in alert.errors">
					{{error}}
				</p>
			</div>

		</section>

		<section v-if="$route.params.id != 'new'">
			<ul class="nav nav-tabs">
				<li :class="{active: activeTab == 'locations'}"><a href="#" v-on:click="activeTab = 'locations'">Locations</a></li>
				<li :class="{active: activeTab == 'audits'}"><a href="#" v-on:click="activeTab = 'audits'">Audits</a></li>
				<li :class="{active: activeTab == 'reports'}"><a href="#" v-on:click="activeTab = 'reports'">Reports</a></li>

			</ul>

			<div class="tab-content">
				
				<div v-if="activeTab == 'locations'">
					<h2>Locations</h2>
					<button class="btn btn-success" v-on:click="newLocation"><i class="fa fa-plus"></i> New Location</button>

					<search-widget v-model="locationFilterTerms" v-on:submit="filterLocations"></search-widget>

					<table-list
						:records="locations"
						:fields="locationFields"
						:has-edit="true"
						delete-endpoint="location/delete"
						:has-delete="true"
						v-on:edit="editLocation">
					</table-list>
				</div>

				<div v-if="activeTab == 'audits'">
					<h2>Audits</h2>

					<button class="btn btn-success" v-on:click="auditDialog.visible = true"><i class="fa fa-plus"></i> New Audit</button>

					<search-widget v-model="auditFilterTerms" v-on:submit="filterAudits"></search-widget>

					<table-list 
						:records="audits" 
						:fields="auditFields" 
						has-edit="/audits/form/" 
						:has-delete="true" 
						v-on:modelChange="filterAudits" 
						delete-endpoint="auditForm/delete">
						
					</table-list>
				</div>

				<div v-if="activeTab == 'reports'">
					<div v-if="!$root.isOffline">
						<h2>Reports</h2>

						<router-link class="btn btn-success" to="/reports/new"><i class="fa fa-plus"></i> New Report</router-link>

						<!--<search-widget v-model="reportFilterTerms" v-on:submit="filterReports"></search-widget>-->

						<table-list 
							:records="amendedReports" 
							:fields="reportFields" 
							has-edit="/reports/edit/" 
							:has-delete="true" 
							v-on:modelChange="filterReports" 
							delete-endpoint="/report/delete">
							
						</table-list>
					</div>
					<page-offline-alert v-else></page-offline-alert>
				</div>

			</div>
			
		</section>

		<modal-dialog
            v-if="locationDialog.visible" 
            :title="locationDialog.title" 
            :modal-visible="locationDialog.visible" 
            :confirm-button-text="locationDialog.buttonText"
            :button-class="locationDialog.buttonClass"
            v-on:confirm="saveLocationEdit(locationDialog.fields)"
            v-on:closeModal="locationDialog.visible = false">
            
            <form-group v-for="field in locationFields" :key="field.key" :label="field.label" col-class="col-md-4">
            	<input type="text" class="form-control" v-model="locationDialog.fields[field.key]">
            </form-group>

        </modal-dialog>

        <modal-dialog
            v-if="auditDialog.visible" 
            :title="auditDialog.title" 
            :modal-visible="auditDialog.visible" 
            :confirm-button-text="auditDialog.buttonText"
            :button-class="auditDialog.buttonClass"
            v-on:confirm="createNewAudit"
            v-on:closeModal="auditDialog.visible = false">
            
            <form-group label="Location" col-class="col-md-4">
            	<select class="form-control" v-model="auditDialog.fields.location_id" required>
            		<option v-for="location in locations" :value="location.id" >{{ location.location_name }}</option>
            	</select>
            </form-group>

			<form-group label="Audit Date" col-class="col-md-4">
				<date-field extra-classes='form-control' v-model="auditDialog.fields.audit_date" ></date-field>
			</form-group>

            <form-group label="Form Template" col-class="col-md-4">
            	<select class="form-control" v-model="auditDialog.fields.form_template_id" required>
            		<option v-for="template in auditTemplates" :value="template.id">{{ template.form_name }}</option>
            	</select>
            </form-group>

        </modal-dialog>
	</div>

</template>

<script type="text/javascript">
	import FormGroup from '../components/FormGroup'
	import TableList from '../components/TableList'
	import ModalDialog from '../components/ModalDialog'
	import SearchWidget from '../components/SearchWidget'
	import DateField from '../components/DateField'
	import PageOfflineAlert from '../components/PageOfflineAlert'

	export default {
		name: "ClientEditor",
		props: ["clients"],
		components: {
			FormGroup,
			TableList,
			ModalDialog,
			SearchWidget,
			DateField,
			PageOfflineAlert
		},
		data () {
			return {
				alert: {
					visible : false,
					class: "",
					msg: "",
					hasErrors: false,
					errors: []
				},
				client: {},
				locations: [],
				locationFilterTerms: "",
				auditFilterTerms: "",
				reportFilterTerms: "",
				locationFields: [
					{
						label: "Location Name",
						key: "location_name"
					},
					{
						label: "Address",
						key: "address"
					},
					{
						label: "City",
						key: "city"
					},
					{
						label: "Province",
						key: "province"
					},
					{
						label: "Postal Code",
						key: "postal_code"
					},
					{
						label: "Phone",
						key: "phone"
					},
				],
				locationDialog: {
					visible: false,
					title: "Edit Client Location",
					buttonText: "Save",
					buttonClass: "btn-success",
					fields: {}
				},
				auditDialog: {
					visible: false,
					title: "New Audit",
					buttonText: "Create",
					buttonClass: "btn-success",
					fields: {
						location_id: null,
						client_id: this.$route.params.id,
						audit_date: null,
						form_template_id: null
					}
				},
				auditFields: [
					{
						key: "audit_date",
						label: "Audit Date"
					},
					{
						key: "client_company",
						label: "Client"
					},
					{
						key: "client_location",
						label: "Location"
					},
					{
						key: "form_template_name",
						label: "Form"
					},

				],
				audits: [],
				reportFields: [
					{
						key: "report_title",
						label: "Report Title"
					},
					{
						key: "contact_name",
						label: "Client Contact"
					},
					{
						key: "company",
						label: "Client Company"
					},
					{
						key: "date_issued",
						label: "Date Issued"
					},

				],
				reports: [],
				activeTab: 'locations',
				auditTemplates: []
			}
		},
		computed: {
			amendedReports() {
				let amended = []

				this.reports.forEach(function(report,index) {
					report['contact_name'] = report.clients.contact_name
					report['company'] = report.clients.company

					amended.push(report)
				})

				return amended
			}
		},
		methods: {
			save() {
				let payload = this.client;

				delete payload.locations;
				
				let vm = this

				this.postData(window.apiBase+"client/save",payload).then(function(response) {
					vm.$emit("updateAlert",{
						visible: true,
						class: "alert-success",
						msg: "Client saved"
					})

					vm.$router.replace("/clients/"+response.id)

					vm.$emit("clientListUpdate")
				})
			},
			editLocation(record) {
				this.locationDialog.fields = record
				this.locationDialog.visible = true
			},
			newLocation() {
				this.locationDialog.visible = true
				
				let vm = this

				this.locationDialog.fields = {
					client_id: this.$route.params.id
				}
				this.locationFields.forEach(function(field, index){
					vm.locationDialog.fields[field] = ""
				})
			},
			saveLocationEdit(record) {
				let vm = this

				this.postData(window.apiBase+"location/save",record).then(function(response){
					vm.locationDialog.visible = false

					vm.fetchLocations()

				})
			},
			createNewAudit() {
				let vm = this

				this.postData(window.apiBase+"auditForm/save",this.auditDialog.fields).then(function(response){
					vm.auditDialog.visible = false

					vm.filterAudits()
				})
			},
			filterLocations()
			{
				let vm = this

				let filters = JSON.stringify([
					["client_id",this.$route.params.id,'and'],
					['location_name',this.locationFilterTerms,'and','like'],
					['address',this.locationFilterTerms,'or','like'],
					['province',this.locationFilterTerms,'or','like'],
					['city',this.locationFilterTerms,'or','like'],
					['postal_code',this.locationFilterTerms,'or','like']

				])

				let order = JSON.stringify(["location_name","ASC"]);

				this.getJSON(window.apiBase + "location/get?filters="+filters+"&order="+order).then(function(response){
					vm.locations = response
				})
			},
			filterAudits() {
				let vm = this

				let filters = "";
				if (this.auditFilterTerms) {
					filters = JSON.stringify([
						["audits.client_id",this.$route.params.id,'and'],
						["location_name",this.auditFilterTerms,'and','like'],
						["company",this.auditFilterTerms,'or','like'],
						["audit_date",this.auditFilterTerms,'or','like'],
						["form_name",this.auditFilterTerms,'or','like']
					])
				}

				this.getJSON(window.apiBase + "auditForm/get?filters="+filters).then(function(response){
					vm.audits = response

					vm.audits.forEach(function(audit, index){
						vm.$set(audit,"client_company",audit.clients.company)
						vm.$set(audit,"client_location",audit.locations.location_name)
						vm.$set(audit,"form_template_name",audit.form_templates.form_name)
					})

					vm.$emit("toggleSpinner",false)
				})
			},
			filterReports() {
				let vm = this

				let filters = "";
				if (this.auditFilterTerms) {
					filters = JSON.stringify([
						["reports.client_id",this.$route.params.id,'and']
					])
				}

				this.getJSON(window.apiBase + "report/get?filters="+filters).then(function(response){
					vm.reports = response

					vm.$emit("toggleSpinner",false)
				})
			},
			fetchClient() {
				let vm = this
				let id = this.$route.params.id

				this.getJSON(window.apiBase + "client/find/"+id).then(function(response){
					vm.client = response
					vm.$emit("toggleSpinner",false)
				})
			}
		},
		created() {
			if (this.$route.params.id != "new") {
				this.fetchClient()
				this.fetchLocations()
				this.filterAudits()
				this.filterReports()
				this.fetchAuditTemplates()

				localStorage.currentClientId = this.$route.params.id
			}
			else
				this.$emit("toggleSpinner",false)
		}
	}
</script>

