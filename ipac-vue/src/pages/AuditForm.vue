<template>
	<section>
		<div class="button-bar">
			<router-link :to="'/clients/'+currentClientId" class='router-link'><i class="fa fa-angle-double-left"></i> Back to Client</router-link>
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
							<date-field extra-classes='form-control' v-model="audit.audit_date" v-on:change="autoSave"></date-field>
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
							<form-group v-for="field in section.fields" :key="field.question" :label="field.question" col-class="col-md-6">

								<yes-no-na-buttons v-if="field.type=='yes/no'" v-model="field.value" v-on:input="autoSave"></yes-no-na-buttons>

								<div v-if="field.type=='images' || field.type == 'image'">
									<div class="thumbs">
										<div 
											v-for="(image,index) in field.value" 
											class="thumb" 
											:style="{backgroundImage: 'url('+apiBase+'image/image/'+image+')'}">

											<button type="button" class="btn btn-danger btn-sm" v-on:click="deleteImage(index, field.value)">
												<i class="fa fa-remove"></i>
											</button>
										</div>
									</div>
									<upload-field v-on:uploaded="saveUpload($event,field)"></upload-field>
								</div>

								<textarea v-if="field.hasNotes" class="form-control" v-on:change="autoSave" placeholder="Notes..." v-model="field.notes"></textarea>
							</form-group>
							
						</div>

						<div v-if="section.subSections" v-for="(subSection, subindex) in section.subSections" class="form-subsection">

							<h3 v-if="subSection.heading">{{index+1}}.{{subindex+1}} {{ subSection.heading }}</h3>

							<div v-if="subSection.fields">

								<form-group v-for="subSectionField in subSection.fields" :key="subSection.question" :label="subSectionField.question" col-class="col-md-6">
									<yes-no-na-buttons v-if="subSectionField.type=='yes/no'" v-model="subSectionField.value" v-on:input="autoSave"></yes-no-na-buttons>

									<div v-if="subSectionField.type=='images' || subSectionField.type == 'image'">
										<div class="thumbs">
											<div 
												v-for="(image,index) in field.value" 
												class="thumb" 
												:style="{backgroundImage: 'url('+apiBase+'image/image/'+image+')'}">

												<button type="button" class="btn btn-danger btn-sm" v-on:click="deleteImage(index, subSectionField.value)">
													<i class="fa fa-remove"></i>
												</button>
											</div>
										</div>
										<upload-field v-on:uploaded="saveUpload($event,subSectionField)"></upload-field>
									</div>

									<textarea v-if="subSectionField.hasNotes" class="form-control" placeholder="Notes..." v-model="subSectionField.notes"></textarea>
								</form-group>

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



	</section>

</template>

<script type="text/javascript">
	import FormGroup from '../components/FormGroup'
	import YesNoNaButtons from '../components/YesNoNaButtons'
	import DateField from '../components/DateField'
	import UploadField from '../components/UploadField'

	export default {
		name: "AuditForm",
		props: [],
		components: {
			FormGroup,
			YesNoNaButtons,
			DateField,
			UploadField
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
				tocCollapsed: false
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
			deleteImage(index,images) {
				let payload = {
					filename: images[index]
				}

				let vm = this

				this.postData(window.apiBase+"image/delete",payload).then(function(response){
					if (response.status == "success") {
						images.splice(index,1)
					}

					vm.autoSave()
				})
			},
			saveUpload(response, field) {
				
				if (!field.value)
					field.value = []

				field.value.push(response.filename)

				this.autoSave()

			},
			autoSave() {

				if (this.autoSaveActive) { //we need to prevent autosave from firing on load when controls get bound
					this.$emit("updateAlert",{
						visible:true,
						class: "alert-info",
						msg: "Saving...",
						icon: "fa-spin fa-spinner"
					})

					let vm = this

					this.audit.form_values = this.form

					vm.postData(window.apiBase+"auditForm/save",vm.audit).then(function(response){
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
					})
				}

			},
			fetchAudit() {
				let vm = this

				this.getJSON(window.apiBase + "auditForm/find/"+this.$route.params.id).then(function(response){

					if (response.form_values)
						vm.form = JSON.parse(response.form_values)
					else {
						vm.form = JSON.parse(response.form_templates.fields)

						vm.form.forEach(function(section, index){
							if ("fields" in section) {
								section.fields.forEach(function(field,field_index){
									vm.$set(field,"value",null)
									vm.$set(field,"notes",null)
								})
							}
							if ("subSections" in section) {
								section.subSections.forEach(function(subSection, sub_index){
									subSection.fields.forEach(function(field, field_index){
										vm.$set(field,"value",null)
										vm.$set(field,"notes",null)
									})
								})
							}
						})
					}

					vm.fullAudit = response

					vm.audit = {
						audit_date: response.audit_date,
						client_id: response.client_id,
						form_template_id: response.form_template_id,
						form_values: response.form_values,
						id: response.id,
						location_id: response.location_id,
					}

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

	.thumbs .thumb {
		display: inline-block;
		height: 80px;
		width: 100px;
		background-size: cover;
		background-position: center center;
		background-repeat: no-repeat;
		position: relative;
	}

	.thumbs .thumb button {
		position: absolute;
		top: 0;
		right: 0;
	}

	@media (max-width: 991px) {
		.section-toc {
			margin-bottom: 10px;
		}
	}

</style>

