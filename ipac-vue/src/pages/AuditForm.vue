<template>
	<section>
		<div class="button-bar">
			<router-link :to="'/clients/'+currentClientId+'/audits'" class='router-link'><i class="fa fa-angle-double-left"></i> Back to Client</router-link>

			<div class="pull-right">
				<a :href="docUrl" class="btn btn-primary"><i class="fa fa-file-word-o"></i> Audit to Word</a>
			</div>
		</div>

		<div class="text-center" style="margin-bottom: 30px;">
			<p>The audit is 
				<strong v-if="!audit.locked"><i class="fa fa-unlock"></i> EDITABLE</strong>
				<strong v-else-if="audit.locked== 1"><i class="fa fa-lock"></i> LOCKED</strong>
				<span> &nbsp; &nbsp; </span>
				<button v-if="audit.locked == 1" v-on:click="lockUnlockAudit" class="btn btn-default">
					<i class="fa fa-unlock"></i>
					Un-Lock
				</button>
				<button v-else v-on:click="lockUnlockAudit" class="btn btn-default">
					<i class="fa fa-lock"></i>
					Lock
				</button>
			</p>

		</div>

		<div class="row">

			<div class="col-md-4">
				<div class="section-toc">
					<h2 v-on:click="tocCollapsed ? tocCollapsed = false : tocCollapsed = true">Sections
						<i class="fa pull-right" :class="{'fa-caret-up' : !tocCollapsed, 'fa-caret-down': tocCollapsed}"></i>
					</h2>

					<ul v-show="!tocCollapsed" class="nav nav-pills nav-stacked">

						<li v-on:click="activeSection = -1" :class="{'active':activeSection == -1}">
							<a href="#">Audit Details</a>
						</li>

						<li v-for="(section, index) in form" :class="{'active':activeSection == index}" v-on:click="activeSection = index">
							<div v-if="section.totalFields - section.totalFieldsAnswered > 0" class="alert-bubble" title="Unanswered fields">
								{{ section.totalFields - section.totalFieldsAnswered }}
							</div>
							<a href="#">{{ index+1 }}.0 {{ section.heading }}</a>
						</li>

					</ul>
				</div>
			</div>
			<div class="col-md-8">
				<form class="form-horizontal">

					<div class="form-section" v-show="activeSection == -1">
						<h2>Audit Details</h2>

						<form-group label="Client" col-class="col-md-4">
							<select class="form-control" v-model="audit.client_id" v-on:change="autoSave">
								<option>Select a Client...</option>
								<option v-for="client in clients" :value="client.id">{{client.company}}</option>
							</select>
						</form-group>

						<form-group label="Location" col-class="col-md-4">
							<select class="form-control" v-model="audit.location_id" v-on:change="autoSave">
								<option v-for="location in selectedClientLocations" :value="location.id">{{ location.location_name }}</option>
							</select>
						</form-group>

						<form-group label="Audit Date" col-class="col-md-4">
							<date-field v-model="audit.audit_date" v-on:change="autoSave"></date-field>
						</form-group>

						<button v-on:click="activeSection++" type="button" class="btn btn-default pull-right">
							Next Section
							<i class="fa fa-arrow-circle-right"></i>
						</button>
						<div style="clear: both;"></div>
				
					</div>

					<div v-for="(section, index) in form" class="form-section" v-show="index == activeSection">
						<h2 v-if="section.heading">{{ index+1 }}.0 {{section.heading}}</h2>

						<div v-if="section.fields">
							<p v-if="audit.locked == 1" v-show="false">The audit is locked, so these fields aren't editable</p>
							<audit-field 
								v-if="audit.locked == 0"
								v-for="(field, fieldIndex) in section.fields" 
								:key="field.question" 
								:label="field.question" 
								:field="field" 
								:value="field.value" 
								v-on:change="autoSave" 
								v-on:imageFieldChange="saveImageFieldChange($event,index, fieldIndex, false)">	
							</audit-field>

							<form-group
								v-else
								v-for="(field, fieldIndex) in section.fields"
								:key="field.question" 
								:label="field.question"
								col-class="col-md-6">

								<image-upload-field v-if="field.type == 'images'"
									:images="field.value"
									:locked="true"
									:api-base="apiBase">
								</image-upload-field>

								<div v-else class="form-control-plaintext"><div class="field-text-answer">{{ field.value }}</div></div>

								<strong v-if="field.hasNotes">Notes:</strong>
								<div v-if="field.hasNotes" class="form-control-plaintext">{{ field.notes }}</div>

								<hr>

							</form-group>
						</div>

						<div v-if="section.subSections" v-for="(subSection, subindex) in section.subSections" class="form-subsection">

							<h3 v-if="subSection.heading">{{index+1}}.{{subindex+1}} {{ subSection.heading }}</h3>



							<div v-if="subSection.fields">

								<p v-if="audit.locked == 1" v-show="false">The audit is locked, so these fields aren't editable</p>

								<audit-field 
									v-for="(field, fieldIndex) in subSection.fields" 
									v-if="audit.locked == 0"
									:key="field.question" 
									:label="field.question" 
									:field="field" 
									:value="field.value" 
									v-on:change="autoSave" 
									v-on:imageFieldChange="saveImageFieldChange($event,index, fieldIndex, true)">
								</audit-field>

								<form-group
									v-else
									v-for="(field, fieldIndex) in subSection.fields"
									:key="field.question" 
									col-class="col-md-6"
									:label="field.question">

									<image-upload-field v-if="field.type == 'images'"
										:images="field.images"
										:locked="true"
										:api-base="apiBase">
									</image-upload-field>
									<div v-else class="form-control-plaintext"><div class="field-text-answer">{{ field.value }}</div></div>

									<strong v-if="field.hasNotes">Notes:</strong>
									<div v-if="field.hasNotes" class="form-control-plaintext">{{ field.notes }}</div>

								</form-group>

								<hr>

							</div>
						</div>

						<button v-on:click="activeSection--" type="button" class="btn btn-default"> 
							<i class="fa fa-arrow-circle-left"></i>
							Previous Section
						</button>

						<button v-if="form.length > index + 1" type="button" v-on:click="activeSection++" class="btn btn-default pull-right">
							Next Section
							<i class="fa fa-arrow-circle-right"></i>
						</button>
						<div style="clear: both;"></div>
					</div>
				</form>
			</div>
		</div>

		<modal-dialog
	        v-if="confirmDialog.visible" 
	        title="Mark Task as Completed" 
	        :modal-visible="confirmDialog.visible" 
	        confirm-button-text="Yes"
	        button-class="btn-primary"
	        v-on:confirm="completeTask"
	        v-on:closeModal="confirmDialog.visible = false">
	        You've completed your audit form! Would you like to mark the linked task as complete?
	    </modal-dialog>

	</section>

</template>

<script type="text/javascript">
	import FormGroup from '../components/FormGroup'
	import ModalDialog from '../components/ModalDialog'
	import AuditField from '../components/AuditField'
	import DateField from '../components/DateField'
	import ButtonGroup from '../components/ButtonGroup'
	import ImageUploadField from '../components/ImageUploadField'

	export default {
		name: "AuditForm",
		components: {
			FormGroup,
			ModalDialog,
			AuditField,
			DateField,
			ButtonGroup,
			ImageUploadField
		},
		props: ["clients"],
		data () {
			return {
				form: {},
				audit: {},
				activeSection: -1,
				fullAudit: {},
				autoSaveActive: false,
				apiBase: window.apiBase,
				currentClientId: localStorage.currentClientId,
				tocCollapsed: false,
				confirmDialog: {
					visible: false,
				},
				apiBase: window.apiBase,
				docUrl: window.apiBase+"auditForm/word/"+this.$route.params.id+"?key="+localStorage.apiKey,
			}
		},
		watch: {
			'audit.form_values': function() {
				let totalAnswered = 0

				let totalFields = 0;
				this.form.forEach(function(section, index){
					totalFields += section.totalFields
					totalAnswered += section.totalFieldsAnswered
				})

				let totalUnanswered = totalFields - totalAnswered

				// Audit is complete, show prompt to mark related task if its not already marked as complete as well
				if (totalUnanswered == 0 && this.audit.task_id != 0) {
					//check to make sure task isn't already marked as complete

					let vm = this

					let task_id = this.audit.task_id

					if (task_id != 0) {

						//get task to check its status
						this.getJSON(window.apiBase+"task/find/"+task_id).then(function(response){
							if (response.completed_at == null)
								vm.confirmDialog.visible = true
						})
					}
					
				}

			}
		},
		computed: {
			selectedClientLocations() {
				let vm = this

				let client_locations = []
				if (this.audit) {
					this.clients.forEach(function(client, index){
						if (client.id == vm.audit.client_id) {
							client_locations = client.locations
						}
					})
				}

				return client_locations
			}
		},
		methods: {
			lockUnlockAudit() {
				let vm = this

				let locked = vm.audit.locked == 1 ? 0 : 1

				let payload = {
					locked: locked,
					id: vm.audit.id
				}

				let icon = "fa-lock"
				let msg = "Audit is now Locked"
				if (locked == 0) {
					icon = "fa-unlock"
					msg = "Audit is now Un-locked!"
				}

				console.log(locked)

				this.postData(window.apiBase+"auditForm/save",payload).then(response => {
					if (response.status == "success") {
						vm.audit.locked = locked

						vm.$emit("updateAlert",{
							visible:true,
							class: "alert-success",
							msg: msg,
							icon: icon
						})
					}
				})
			},
			completeTask() {
				let vm = this

				let payload = {
					id: this.audit.task_id,
					completed_at: "{{{server_now}}}",
					completed_by: "{{{current_user}}}"
				}

				this.postData(window.apiBase+"task/save",payload).then(function(response){
					vm.confirmDialog.visible = false
				})
			},
			
			calcTotalFieldsAnswered() {
				//calculate total fields and total fields answered
				let vm = this
				this.form.forEach(function(section, index){

					let sectiontotalFields = 0;
					let sectionTotalAnswered = 0;

					if ("fields" in section) {
						section.fields.forEach(function(field,field_index){
							sectiontotalFields++;
							if ("value" in field)
								sectionTotalAnswered++
						})
					}
					if ("subSections" in section) {
						section.subSections.forEach(function(subSection, sub_index){
							subSection.fields.forEach(function(field, field_index){
								sectiontotalFields++;
								if ("value" in field)
									sectionTotalAnswered++
							})
						})
					}

					if ("totalFields" in section)
						section.totalFields = sectiontotalFields
					else
						vm.$set(section,"totalFields",sectiontotalFields)

					if ("totalFieldsAnswered" in section)
						section.totalFieldsAnswered = sectionTotalAnswered
					else
						vm.$set(section,"totalFieldsAnswered",sectionTotalAnswered)
				})
			},
			saveImageFieldChange(newValue, index, fieldIndex, isSubField) {

				if (isSubField) 
					this.form[index].subSections[fieldIndex].fields[fieldIndex].value = newValue
				else
					this.form[index].fields[fieldIndex].value = newValue

				this.autoSave()

			},
			autoSave() {

				//recalculated number of fields unanswereed for this section

				if (this.autoSaveActive) { //we need to prevent autosave from firing on load when controls get bound
					this.$emit("updateAlert",{
						visible:true,
						class: "alert-info",
						msg: "Saving...",
						icon: "fa-spin fa-spinner"
					})

					let vm = this

					this.audit.form_values = JSON.stringify(this.form)

					////////////////////////////////
					// LOCAL SAVE
					////////////////////////////////
					let localAuditBackups = "localAuditBackups" in localStorage ? JSON.parse(localStorage.localAuditBackups) : []

					//check limit
					if ("localAuditBackups" in localStorage && localStorage.localAuditBackups.length > 3000000) {
						
						localAuditBackups.splice(0,1)
					}

					//get client name
					let clientName = ""
					this.clients.forEach(client => {
						if (client.id == vm.audit.client_id)
							clientName = client.company
					})

					//get location
					let auditLocation = ""
					this.selectedClientLocations.forEach(location => {
						if (location.id == vm.audit.location_id)
							auditLocation = location.location_name
					})

					localAuditBackups.push({
						clientName: clientName,
						auditLocation: auditLocation,
						auditDate: vm.audit.audit_date,
						auditData: vm.audit
					})

					localStorage.localAuditBackups = JSON.stringify(localAuditBackups)

					this.calcTotalFieldsAnswered()

					vm.audit.performed_by = "{{{current_user}}}"

					/////////////////////////
					// SAVE TO SERVER
					/////////////////////////

					vm.postData(window.apiBase+"auditForm/save",vm.audit).then(function(response){

						if (response.status == "success") {
							vm.$emit("updateAlert",{
								visible:true,
								class: "alert-success",
								msg: "Saved!",
								icon: "fa-save"
							})

							setTimeout(function(){
								vm.$emit("updateAlert",{
									visible:false,
								})
							},6000)
						}
						else {
							let msg = "A problem occurred, the save did not occur. Check your internet connection."

							if ("msg" in response)
								msg = response.msg

							vm.$emit("updateAlert",{
								visible:true,
								class: "alert-danger",
								msg: msg,
								icon: "fa-alert"
							})

							if ("reason" in response && response.reason == "locked") {
								vm.autoSaveActive = false

								vm.fetchAudit()
							}
						}
					})
					
				}

			},
			fetchAudit() {
				let vm = this

				this.getJSON(window.apiBase + "auditForm/find/"+this.$route.params.id).then(function(response){

					if (response.form_values)
						vm.form = JSON.parse(response.form_values)
					else 
						vm.form = JSON.parse(response.form_templates.fields)
					

					vm.fullAudit = response

					vm.audit = {
						audit_date: response.audit_date,
						client_id: response.client_id,
						form_template_id: response.form_template_id,
						id: response.id,
						location_id: response.location_id,
						task_id: response.task_id,
						locked: response.locked
					}

					vm.$set(vm.audit,"form_values",response.form_values)

					console.log(vm.form)

					vm.calcTotalFieldsAnswered()
					
					//we're ready, turn on autosave after short delay
					setTimeout(function(){
						vm.autoSaveActive = true
					},1000)

					vm.$emit("toggleSpinner",false)
				})
			},
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.fetchAudit();
		}
	}
</script>

<style type="text/css" scoped>

	.form-section {
		padding: 10px;
		padding-top: 0;
	}

	.section-toc {
		background-color: #EBF5FF;
		padding: 10px;
		padding-top: 1px;
	}

	.section-toc h2:hover {
		cursor: pointer;
		text-decoration: underline;
	}

	form h2 {
		padding: 10px;
		margin: 0;
		margin-bottom: 10px;
		background-color: #337ab7;
		color: white;
	}

	h3 {
		font-size: 14pt;
		text-decoration: underline;
	}

	.alert-bubble {
		float: right;
		padding: 1px;
		padding-left: 3px;
		padding-right: 3px;
		background-color: red;
		color: white;
		box-shadow: 1px 1px 1px black;
		border-radius: 4px;
		text-align: center;
		font-weight: bold;
		font-size: 9pt;
		margin-top: 8px;
		position: relative;
		z-index: 1;
		min-width: 20px;
		margin-left: 3px;

		border-radius: 50%;
	    background-color: red;
	    color: white;
	    padding: 3px;
	    font-size: 8pt;
	    height: 18px;
	    width: 18px;
	    display: inline-block;
	    text-align: center;
	    padding-top: 2px;

	    box-shadow: 1px 1px 2px black;
	}

	.field-text-answer {
		text-transform: uppercase;
		padding: 2px;
		display: inline-block;
		border: 1px solid #a0a0a0;
	}

	@media (max-width: 991px) {
		.section-toc {
			margin-bottom: 10px;
		}
	}

</style>

