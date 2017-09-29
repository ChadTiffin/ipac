<template>
	<div>

		<section>
			<div class="button-bar">
				<router-link to="/clients" class='router-link'><i class="fa fa-angle-double-left"></i> Back to Clients</router-link>

				<nav-tabs classes="pull-right" v-if="$route.params.id != 'new'">

					<li :class="{active: activeTab == 'locations'}"><router-link to="locations">
						<i class="fa fa-map-marker"></i>
						Locations
					</router-link></li>
					<li :class="{active: activeTab == 'audits'}"><router-link to="audits" >
						<i class="fa fa-balance-scale"></i>
						Audits
					</router-link></li>
					<li :class="{active: activeTab == 'reports'}"><router-link to="reports">
						<i class="fa fa-file-pdf-o"></i>
						Reports
					</router-link></li>

					<li :class="{active: activeTab == 'phases'}"><router-link to="phases" >
						<i class="fa fa-forward"></i>
						Phases
					</router-link></li>

					<li :class="{active: activeTab == 'tasks'}"><router-link to="tasks">
						<i class="fa fa-check-square-o"></i>
						Tasks
					</router-link></li>

					<li :class="{active: activeTab == 'expenses'}"><router-link to='expenses' >
						<i class="fa fa-money"></i>
						Expenses
					</router-link></li>

				</nav-tabs>

				<div style="clear: both;"></div>
				
			</div>

			<div class="row">
				<div class="col-md-4">
					<div class="well">

						<div class="pull-right">

							<router-link v-if="$route.params.id == 'new'" to="/clients" class="btn btn-default">Cancel</router-link>
							<button v-if="!$root.isOffline" class="btn btn-success"><i class="fa fa-save"></i> Save</button>

						</div>

						<h2>Client Details</h2>

						<div style="clear: both;"></div>
						<form class="form-horizontal" v-on:submit.prevent="saveClientDetails">

							<form-group label="Company" col-class="col-md-3">
								<input v-if="!$root.isOffline" type="text" v-model='client.company' class="form-control">
								<p v-else class="form-control-static">{{ client.company }}</p>
							</form-group>

							<form-group label="Contact Name" col-class="col-md-3">
								<input v-if="!$root.isOffline" type="text" v-model='client.contact_name' class="form-control">
								<p v-else class="form-control-static">{{ client.contact_name }}</p>
							</form-group>

							<form-group label="Primary Phone" col-class="col-md-3">
								<input v-if="!$root.isOffline" type="tel" v-model='client.primary_phone' class="form-control">
								<p v-else class="form-control-static">{{ client.primary_phone }}</p>
							</form-group>

							<form-group label="Email" col-class="col-md-3">
								<input v-if="!$root.isOffline" type="text" v-model='client.email' class="form-control">
								<p v-else class="form-control-static">{{ client.email }}</p>
							</form-group>

							<form-group label="Address" col-class="col-md-3">
								<input v-if="!$root.isOffline" type="text" v-model='client.address' class="form-control">
								<p v-else class="form-control-static">{{ client.address }}</p>
							</form-group>

							<form-group label="City" col-class="col-md-3">
								<input v-if="!$root.isOffline" type="text" v-model='client.city' class="form-control">
								<p v-else class="form-control-static">{{ client.city }}</p>
							</form-group>

							<form-group label="Province" col-class="col-md-3">
								<input v-if="!$root.isOffline" type="text" v-model='client.province' class="form-control">
								<p v-else class="form-control-static">{{ client.province }}</p>
							</form-group>

							<form-group label="Postal Code" col-class="col-md-3">
								<input v-if="!$root.isOffline" type="text" v-model='client.postal_code' class="form-control">
								<p v-else class="form-control-static">{{ client.postal_code }}</p>
							</form-group>

							
							<div style="clear: both;"></div>

						</form>
						<div class="alert" :class="alert.class" v-if="alert.visible">
							{{ clientDialog.alert.msg }}
							<p v-if="alert.hasErrors" v-for="error in alert.errors">
								{{error}}
							</p>
						</div>
						<button v-if="$route.params.id != 'new'" class="btn btn-danger block-button" style="margin-bottom: 10px;" v-on:click="deleteClient"><i class="fa fa-times"></i> Delete Client</button>
					</div>

					<div class="well" v-if="activeTab == 'tasks'">
						<h3>Filter Tasks</h3>
						<button-group
							vertical="true" 
							:buttons="taskFilter.buttons" 
							:full-width="true"
							v-model="taskFilter.value">
						</button-group>
					</div>
				</div>

				<div class="col-md-8">

					<div class="tab-content">
						
						<div v-if="activeTab == 'locations'">
							<h2>Locations</h2>
							<button v-if="!$root.isOffline" class="btn btn-success" v-on:click="newLocation"><i class="fa fa-plus"></i> New Location</button>

							<search-widget v-model="locationFilterTerms" v-on:submit="filterLocations"></search-widget>

							<table-list
								:records="locations"
								:fields="locationFields"
								:has-edit="true"
								delete-endpoint="location/delete"
								:has-delete="true"
								v-on:modelChange="filterLocations" 
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

								<router-link v-if="!$root.isOffline" class="btn btn-success" to="/reports/new"><i class="fa fa-plus"></i> New Report</router-link>

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

						<div v-if="activeTab == 'tasks'">
							<div v-if="!$root.isOffline">

								<task-list :editable="true" :heading="taskFilter.value + ' Tasks'" :include-new-button="true" :tasks="filteredTasks"  v-on:modelChanged="fetchTasks"></task-list>
	
							</div>
							<page-offline-alert v-else></page-offline-alert>
						</div>

						<div v-if="activeTab == 'phases'">
							<div v-if="!$root.isOffline">

								<phase-list :phases="clientPhases" :editable="true" owner-type="client" v-on:phasesChanged="fetchPhases"></phase-list>

							</div>
							<page-offline-alert v-else></page-offline-alert>
						</div>

						<div v-if="activeTab == 'expenses'">
							<div v-if="!$root.isOffline">
								
								<h2>Client Expenses for {{ $root.userFullName }}</h2>

								<expense-list owner-type="client" :locations="locations"></expense-list>

							</div>
							<page-offline-alert v-else></page-offline-alert>
						</div>

					</div>
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

            <div class="col-md-offset-4 checkbox">
            	<label>
            		<input type="checkbox" v-model="auditDialog.generateTask">
            		Generate a task that is linked to this Audit
            	</label>
            </div>

        </modal-dialog>

        <modal-dialog
            v-if="confirmDialog.visible" 
            :title="confirmDialog.title" 
            :modal-visible="confirmDialog.visible" 
            :confirm-button-text="confirmDialog.buttonText"
            :button-class="confirmDialog.buttonClass"
            v-on:confirm="executeDelete(confirmDialog.parameters)"
            v-on:closeModal="confirmDialog.visible = false">
            {{ confirmDialog.message }}
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
	import NavTabs from '../components/NavTabs'
	import TaskList from '../components/TaskList'
	import ButtonGroup from '../components/ButtonGroup'
	import PhaseList from '../components/PhaseList'
	import ExpenseList from '../components/ExpenseList'

	export default {
		name: "ClientEditor",
		props: ["clients"],
		components: {
			FormGroup,
			TableList,
			ModalDialog,
			SearchWidget,
			DateField,
			PageOfflineAlert,
			NavTabs,
			TaskList,
			ButtonGroup,
			PhaseList,
			ExpenseList
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
						audit_date: new Date(),
						form_template_id: null,
						task_id: 0
					},
					generateTask: false
				},
				auditFields: [
					{
						key: "audit_date",
						label: "Audit Date"
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
						key: "location_name",
						label: "Location"
					},
					{
						key: "date_issued",
						label: "Date Issued"
					},

				],
				reports: [],
				activeTab: this.$route.params.tab,
				auditTemplates: [],
				confirmDialog: {
                    visible: false,
                    title: "",
                    buttonText: "Delete",
                    buttonClass: "btn-danger",
                    parameters: "",
                    successFunction: ""
                },
                tasks: [],
                taskFilter: {
                	value: "To-Do",
                	buttons: [
                		{
                			text: "To-Do",
                			value: "To-Do"
                		},
                		{
                			text: "Needs Billing",
                			value: "Needs Billing"
                		},
                		{
                			text: "Completed",
                			value: "Completed"
                		}
                	]
                },
                clientPhases: []
			}
		},
		watch: {
			'$route': function(to, from) {
				this.activeTab = to.params.tab

				if (to.params.tab == "locations" || this.$route.params.tab == "expenses")
					this.filterLocations()
				else if (to.params.tab == "tasks") 
					this.fetchTasks()
				else if (to.params.tab == "audits") {
					this.filterAudits()
					this.filterLocations()
				}
			}
		},
		computed: {
			amendedReports() {
				let amended = []

				this.reports.forEach(function(report,index) {
					report['contact_name'] = report.clients.contact_name
					report['company'] = report.clients.company

					report['location_name'] = "[Not set]"
					if (report.locations && "location_name" in report.locations)
						report['location_name'] = report.locations.location_name

					amended.push(report)
				})

				return amended
			},
			filteredTasks() {
				let filtered = [] 

				let vm = this
				this.tasks.forEach(function(task, index){
					if (vm.taskFilter.value == "To-Do" && task.is_complete == 0) {
						filtered.push(task)
					}
					else if (vm.taskFilter.value == 'Needs Billing' && task.is_complete == 1 && task.billable == 1 && task.billed == 0) {
						filtered.push(task)
					}
					else if (vm.taskFilter.value == "Completed" && task.is_complete == 1 && task.billable == 0
					 || vm.taskFilter.value == "Completed" && task.is_complete == 1 && task.billable == 1 && task.billed == 1) {
						filtered.push(task)
					}
				})

				return filtered
				
			}
		},
		methods: {
			saveClientDetails() {
				let payload = this.client;

				delete payload.locations;
				
				let vm = this

				this.postData(window.apiBase+"client/save",payload).then(function(response) {
					vm.$emit("updateAlert",{
						visible: true,
						class: "alert-success",
						msg: "Client saved"
					})

					if (vm.$route.params.id == "new") {
						vm.$router.replace("/clients/"+response.id+"/locations")

						let pageTitle = {
							mainTitle: payload.company_name,
							subTitle: "Client"
						}
						vm.$emit("pageTitle",pageTitle)
					}

					vm.$emit("clientListUpdate")
				})
			},
			deleteClient() {
                this.confirmDialog.title = "Delete "+this.client.company
                this.confirmDialog.message = "Are you sure you want to delete this client?"
                this.confirmDialog.successFunction = "executeDelete"
                this.confirmDialog.buttonText = "Delete"
                this.confirmDialog.buttonClass = "btn-danger"

                this.confirmDialog.visible = true
            },
            executeDelete() {
				let payload = {
                    id: this.client.id
                }

                let vm = this

                this.postData(window.apiBase+"client/delete",payload).then(function(response){
                    vm.$emit("modelChange")
                    vm.confirmDialog.visible = false

                    vm.$emit("updateAlert",{
                        visible: true,
                        class: "alert-danger",
                        msg: "Client deleted"
                    })

                    vm.$router.push("/clients")
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

					vm.filterLocations()

				})
			},
			createNewAudit() {
				let vm = this

				if (this.auditDialog.generateTask) {

					//get template form name
					let templateName = ""
					this.auditTemplates.forEach(function(temp, index){
						if (temp.id == vm.auditDialog.fields.form_template_id)
							templateName = temp.form_name
					})

					let locationName = ""
					this.locations.forEach(function(loc, index){
						if (loc.id == vm.auditDialog.fields.location_id)
							locationName = loc.location_name
					})

					let task_payload = {
						owner_type: "client",
						owner_id: this.$route.params.id,
						description: "'"+templateName+"' Audit at "+locationName + " on "+ moment(this.auditDialog.fields.audit_date).format("YYYY-MM-DD"),
						priority: 1,
						created_by: "{{{current_user}}}",
						created_at: "{{{server_now}}}",
						assigned_to: "{{{current_user}}}"

					}

					this.postData(window.apiBase+"task/save",task_payload).then(function(response){
						vm.auditDialog.fields.task_id = response.id

						//got to wait until server comes back with task id so we can link it to audit form
						vm.postData(window.apiBase+"auditForm/save",vm.auditDialog.fields).then(function(response){
							vm.auditDialog.visible = false

							vm.auditDialog.fields.location_id = 0
							vm.auditDialog.fields.form_template_id = 0
							vm.auditDialog.fields.audit_date = new Date()

							vm.filterAudits()
						})
					})
				}
			},
			filterLocations()
			{
				let vm = this

				let filters = JSON.stringify([
					["client_id",this.$route.params.id],
					['location_name',this.locationFilterTerms,'and','like'],
					["client_id",this.$route.params.id,'or'],
					['address',this.locationFilterTerms,'and','like'],
					["client_id",this.$route.params.id,'or'],
					['province',this.locationFilterTerms,'and','like'],
					["client_id",this.$route.params.id,'or'],
					['city',this.locationFilterTerms,'and','like'],
					["client_id",this.$route.params.id,'or'],
					['postal_code',this.locationFilterTerms,'and','like']

				])

				let order = JSON.stringify(["location_name","ASC"]);

				vm.$emit("toggleSpinner",true)

				this.getJSON(window.apiBase + "location/get?filters="+filters+"&order="+order).then(function(response){
					vm.locations = response

					vm.$emit("toggleSpinner",false)
				})
			},
			filterAudits() {
				let vm = this

				let filters = "";
				if (this.auditFilterTerms) {
					filters = JSON.stringify([
						["audits.client_id",this.$route.params.id,'and'],
						["location_name",this.auditFilterTerms,'and','like'],
						["audits.client_id",this.$route.params.id,'and'],
						["company",this.auditFilterTerms,'or','like'],
						["audits.client_id",this.$route.params.id,'and'],
						["audit_date",this.auditFilterTerms,'or','like'],
						["audits.client_id",this.$route.params.id,'and'],
						["form_name",this.auditFilterTerms,'or','like']
					])
				}
				else {
					filters = JSON.stringify([
						["audits.client_id",this.$route.params.id]
					])
				}

				vm.$emit("toggleSpinner",true)

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

				let filters = JSON.stringify([
					["reports.client_id",this.$route.params.id]
				])

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

					let pageTitle = {
						mainTitle: response.company,
						subTitle: "Client"
					}
					vm.$emit("pageTitle",pageTitle)
				})
			},
			fetchTasks() {
				let vm = this

				vm.$emit("toggleSpinner",true)

				this.getJSON(window.apiBase + "task/filter/client/"+this.$route.params.id).then(function(response){
					vm.tasks = response

					vm.$emit("toggleSpinner",false)
				})
			},
			fetchPhases() {
				let vm = this

				let filter = JSON.stringify([
					["owner_type","client"],
					["owner_id",this.$route.params.id]
				]);

				let order = JSON.stringify(["priority","ASC"]);

				this.getJSON(window.apiBase + "ownerPhase/get?filters="+filter+"&order="+order).then(function(response) {
					vm.clientPhases = response
				})
			}
		},
		created() {
			if (this.$route.params.id != "new") {
				this.fetchClient()
				this.filterAudits()
				this.filterReports()
				this.fetchAuditTemplates()
				this.fetchPhases()

				localStorage.currentClientId = this.$route.params.id

				if (this.$route.params.tab == "locations" || this.$route.params.tab == "expenses")
					this.filterLocations()
				else if (this.$route.params.tab == "tasks") 
					this.fetchTasks()
				else if (this.$route.params.tab == "audits") {
					this.filterAudits()
					this.filterLocations()
				}
			}
			else
				this.$emit("toggleSpinner",false)

			if (typeof this.$route.params.tab == 'undefined' && this.$route.params.id != "new")
				this.$router.replace("/clients/"+this.$route.params.id+"/locations")
		}
	}
</script>

