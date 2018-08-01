<template>
	<section>
		<div class="row">
			<div class="col-md-3">
				<ul>
					<li v-for="form in auditForms" v-on:click="selectAuditForm(form)">{{ form.form_name }}</li>
				</ul>
			</div>
			<div class="col-md-9">
				<form class="form-horizontal" v-on:submit.prevent="save">
					<div v-for="(section,sectionIndex) in auditFormFields" class="audit-section">

						<button type="submit" class="btn btn-success pull-right">
							<i class="fa fa-save"></i>
							Save
						</button>
						<div style="clear: both;margin-bottom: 10px;"></div>

						<h4>Section Heading</h4>
						<input type="text" v-model="section.heading" class="form-control">
						
						<h4>Questions</h4>
						<ul class="list-group">
							<audit-form-editor-field 
								v-if="'fields' in section" 
								v-for="(field,fieldIndex) in section.fields" 
								:key="field.question"
								:input="clone(field)"
								v-on:open="openField(field)"
								v-on:save="saveFieldChanges($event,fieldIndex)"
								v-on:deleteQuestion="deleteQuestion(fieldIndex,false,sectionIndex)">
							</audit-form-editor-field>
						</ul>
						<button type="button" class="btn btn-default" v-on:click="newQuestion(section)">
							<i class="fa fa-plus"></i>
							New Question
						</button>
						

						<!--<div 
							v-if="'fields' in section" 
							v-for="field in section.fields">

							<form-group label="Question Text" col-class="col-md-3">
								<textarea v-model="field.question" class="form-control"></textarea>
							</form-group>

							<div style="clear: both;"></div>
							<hr>
						</div>-->


						<div v-if="'subSections' in section" v-for="(subSection,subsectionIndex) in section.subSections">
							<form-group label="Sub-Section Heading" col-class="col-md-3">
								<input type="text" v-model="subSection.heading" class="form-control">
							</form-group>

							<form-group label="Questions" col-class="col-md-3">
								<ul class="list-group">
									<audit-form-editor-field 
										v-if="'fields' in subSection" 
										v-for="(field,fieldIndex) in subSection.fields" 
										:key="field.question"
										:input="clone(field)"
										v-on:open="openField(field)"
										v-on:save="saveFieldChanges($event,fieldIndex)"
										v-on:deleteQuestion="deleteQuestion(fieldIndex,subsectionIndex,sectionIndex)">
									</audit-form-editor-field>
								</ul>

								<button type="button" class="btn btn-primary" v-on:click="newSubSection(subSection)">
									<i class="fa fa-plus"></i>
									New Sub-Section
								</button>
								<button type="button" class="btn btn-default" v-on:click="newQuestion(subSection)">
									<i class="fa fa-plus"></i>
									New Question
								</button>

							</form-group>

							
						</div>
						<button type="button" class="btn btn-success pull-right" v-on:click="newSection(section)">
							<i class="fa fa-plus"></i>
							New Section
						</button>
						<div style="clear: both;"></div>
					</div>
				</form>
			</div>
		</div>
	</section>	
</template>
<script type="text/javascript">
import AuditFormEditorField from "../components/AuditFormEditorField"
import FormGroup from "../components/FormGroup"	
	
export default {
	name: "AuditEditor",
	components: {
		AuditFormEditorField,
		FormGroup
	},
	data() {
		return {
			auditForms: [],
			selectedForm: {},
			auditFormFields: []
		}
	},
	methods: {
		clone(json) {
			return JSON.parse(JSON.stringify(json))
		},
		selectAuditForm(form) {
			let vm = this

			this.selectedForm = form

			this.auditFormFields = JSON.parse(form.fields)
		},
		openField(field) {
			let vm = this

			field.open = true
		},
		saveFieldChanges(field,fieldIndex) {
			let vm = this

			console.log(this.auditFormFields[0].question)

			this.auditFormFields.splice(fieldIndex,1,field)

			console.log(this.auditFormFields)

			let payload = {
				id: this.selectedForm.id,
				fields: JSON.stringify(this.auditFormFields)
			}

			console.log(payload)

			this.postData(window.apiBase+"AuditFormTemplate/save",payload).then(response => {

			})
		},
		deleteQuestion(fieldIndex,subsectionIndex,sectionIndex) {
			if (subsectionIndex) 	
				delete this.auditFormFields[sectionIndex].subSections[subsectionIndex].fields[fieldIndex]
			else
				delete this.auditFormFields[sectionIndex].fields[fieldIndex]

		},
		newQuestion(section) {
			section.fields.push({
				question: "",
				type: "yes/no",
				hasNotes: true,
				recommendations: ""
			})
		},
		newSubSection(section) {

		},
		newSection(section) {

		},
		fetchAudits() {
			let vm = this

			vm.$emit("toggleSpinner",true)

			this.getJSON(window.apiBase+"AuditFormTemplate/get").then(response => {
				vm.auditForms = response

				vm.$emit("toggleSpinner",false)
			})
		}
	},
	created() {
		this.fetchAudits()
	}
}
</script>

<style type="text/css">
	.audit-section {
		padding: 15px;
		margin-bottom: 10px;
		border-radius: 3px;
		background-color: #efefef;
	}

	.list-group {
		margin-bottom: 10px;
	}
</style>