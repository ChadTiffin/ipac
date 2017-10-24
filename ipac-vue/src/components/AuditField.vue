<template>
	<form-group :label="field.question" :col-class="field.type == 'textarea' || field.type == 'images' ? 'col-md-2' : 'col-md-6'" >

		<yes-no-na-buttons v-if="field.type=='yes/no'" v-model="field.value" v-on:input="fieldChanged"></yes-no-na-buttons>

		<div v-else-if="field.type=='images' || field.type == 'image'">
			<image-upload-field
				v-if="!$root.isOffline" 
				:api-base="apiBase" 
				v-on:imageListChanged="imageFieldChange($event)" 
				:images="value" 
				:multi="true" 
				upload-type="audit-image">		
			</image-upload-field>

			<div v-else class="alert alert-warning">
				<i class="fa fa-warning"></i>
				Can't upload images when offline
			</div>
		</div>

		<counter-field v-else-if="field.type == 'counter'"></counter-field>

		<textarea v-else-if="field.type == 'textarea'" v-model="field.value" v-on:change="fieldChanged" class="form-control"></textarea>

		<textarea v-if="field.hasNotes" class="form-control" v-on:change="fieldChanged" placeholder="Notes..." v-model="field.notes"></textarea>
	</form-group>
</template>

<script type="text/javascript">
import FormGroup from './FormGroup'
import ButtonGroup from "./ButtonGroup"
import YesNoNaButtons from './YesNoNaButtons'
import DateField from './DateField'
import ImageUploadField from './ImageUploadField'
import CounterField from './CounterField'

	export default {
		name: "AuditField",
		components: {
			ButtonGroup,
			DateField,
			ImageUploadField,
			FormGroup,
			YesNoNaButtons,
			CounterField
		},
		props: ["field","value", "colClass"],
		data () {
			return {
				localValue: this.value,
				apiBase: window.apiBase
			}
		},
		watch: {
			value() {
				console.log("value change in AuditField", this.value)
				this.localValue = this.value
			},
			field() {
				console.log("field change in AuditField", this.field)
			}
		},
		methods: {
			imageFieldChange(event) {

				if (!this.localValue)
					this.localValue = []

				if (event.type == "addition") {					
					this.localValue.push(event.response.filename)
				}
				else 
					this.localValue = event.new_images

				/*if (subIndex) 
					this.form[index].subSections[subIndex].fields[fieldIndex].value = field
				else
					this.form[index].fields[fieldIndex].value = field
				*/

				this.$emit("imageFieldChange", this.localValue)

			},
			fieldChanged() {
				this.$emit("change",this.localValue)
				this.$emit("input",this.localValue)
			}
		}
	}
</script>