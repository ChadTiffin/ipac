<template>
	
	<li v-if="isOpen" class="list-group-item" >

		<form-group label="Question Text" col-class="col-md-4 col-sm-4">
			<textarea class="form-control" v-model.lazy="localValues.question"></textarea>
		</form-group> 

		<form-group label="Field Type" col-class="col-md-4 col-sm-4">
			<select class="form-control" v-model="localValues.type">
				<option value="yes/no">Yes/No</option>
				<option value="images">Images</option>
			</select>
		</form-group>

		<form-group label="" col-class="col-md-4 col-sm-4">
			<div class="checkbox">
				<label>
					<input type="checkbox" v-model="localValues.hasNotes" true-value="true" false-value="false">
					Has 'Notes' Text Area
				</label>
			</div>
		</form-group>

		<form-group label="Recommendations Text" col-class="col-md-4 col-sm-4">
			<textarea class="form-control" v-model="localValues.recommendations"></textarea>
		</form-group>

		<div class="pull-left">
			<i class="fa fa-trash fa-2x" title="Delete question" v-on:click="deleteQuestion"></i>
		</div>

		<div class="pull-right">

			<!--<button type="button" class="btn btn-default" v-on:click="isOpen = false">
				<i class="fa fa-times"></i>
				Cancel
			</button>-->
			<button type="button" class="btn btn-success" v-on:click="save">
				<i class="fa fa-save"></i>
				Save
			</button>
		</div>

		<div style="clear: both;"></div>
	</li>
	<li v-else class="list-group-item closed" v-on:click="isOpen = true">
		<i class="fa fa-chevron-down pull-left"></i>
		{{ input.question }}
	</li>
	
</template>
<script type="text/javascript">
import FormGroup from "./FormGroup"	

export default {
	name: "AuditFormEditorField",
	components: {
		FormGroup
	},
	watch: {
		input() {
			this.localValues = this.input
			console.log("input change")
		}
	},
	data() {
		return {
			isOpen: false,
			localValues: null
		}
	},
	props: ["input"],
	methods: {
		save() {
			console.log(this.localValues.question)
			this.$emit("save",this.localValues)
			
			this.isOpen = false
		},
		deleteQuestion() {
			this.$emit("deleteQuestion")
		}
	},
	created() {
		this.localValues = this.input
	}
}
</script>

<style type="text/css" scoped>
	.list-group-item.closed:hover {
		cursor: pointer;
		background-color: #e0e0e0;
	}

	.list-group-item.closed .fa {
		display: none;
	}	

	.list-group-item.closed:hover .fa {
		display: block;
	}

	.fa-trash {
		cursor: pointer;
	}

	.fa-trash:hover {
		color: red;
	}
</style>