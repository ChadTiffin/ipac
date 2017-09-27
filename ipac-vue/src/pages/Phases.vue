<template>
	<section>
		<div class="button-bar">
			<button class="btn btn-success" v-on:click="newPhase"><i class="fa fa-plus"></i> Phase</button>
		</div>

		<p>Drag the phases to re-order.</p>

		<table class="table table-striped">
            <thead>
                <tr>
                	<th></th>
                    <th v-for="field in fields">{{field.label}}</th>
                    <th></th>
                </tr>
            </thead>
            
            <draggable
				element='tbody'
				v-model="phases"
				v-on:update="saveReOrder"
				>

                <tr v-for="phase in phases">
                	<td>
                		<i class="fa fa-arrows"></i>
                	</td>
                    <td>
                        <input type="text" v-model="phase.label" v-on:change="save" class="form-control">
                    </td>
                    <td>

                        <button class="btn btn-danger btn-sm pull-right" v-on:click="deleteRecord(phase)"><i class="fa fa-remove"></i></button>
                    </td>
                </tr>
                <tr v-if="phases.length == 0">
                    <td colspan="3">You don't have any phases</td>
                </tr>
            </draggable>
            <tfoot>
            	<tr>
            		<th colspan="3">
            			<button class="btn btn-success pull-right" v-on:click="newPhase"><i class="fa fa-plus"></i> Phase</button>
            		</th>
            	</tr>
            </tfoot>
        </table>

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

	</section>

</template>

<script type="text/javascript">
	import draggable from 'vuedraggable'
	import ModalDialog from '../components/ModalDialog'

	export default {
		name: "Phases",
		components: {
			draggable,
			ModalDialog
		},
		data () {
			return {
				fields: [
					{
						key: "label",
						label: "Phase Name"
					}
				],
				phases: [],
				confirmDialog: {
					visible: false,
					title: "",
					buttonText: "Delete",
					buttonClass: "btn-danger",
					parameters: "",
					successFunction: ""
				},
			}
		},
		methods: {
			save() {
				let vm = this

				let payload = {
					records: this.phases
				}

				this.postData(window.apiBase+"phase/save-batch",payload).then(function(response){
					vm.$emit("updateAlert",{
						visible: true,
						class: "alert-success",
						msg: "Changes saved."
					})

					vm.fetchPhases()
				})
			},
			saveReOrder() {

				let newPhases = []

				this.phases.forEach(function(phase, index){
					phase.priority = index

					newPhases.push(phase)
				})

				this.phases = newPhases

				this.save()
			},
			deleteRecord(record) {

				let payload = {
					id: record.id
				}

				let vm = this

				this.postData(window.apiBase+"phase/delete",payload).then(function(){
					vm.fetchPhases()

					vm.$emit("updateAlert",{
						visible: true,
						class: "alert-danger",
						msg: "Phase deleted"
					})
				})
			},
			newPhase() {
				let index = this.phases.length

				this.phases.push({
					label: "",
					priority: index
				})
			},
			fetchPhases() {
				let vm = this

				let order = JSON.stringify(["priority","ASC"])

				this.getJSON(window.apiBase+"phase/get?order="+order).then(function(response){

					vm.phases = response

					vm.$emit("toggleSpinner",false)
				})
			}
		},
		created() {
			this.fetchPhases()
		}
	}
</script>

<style type="text/css" scoped>



</style>

