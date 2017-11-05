<template>
	<div>
		<div class="pull-right">
			<button v-if="phases.length > 0 && phases[0].active != 1" class="btn btn-warning" v-on:click="rollback"><i class="fa fa-backward"></i> Rollback Phase</button>

			<button class="btn btn-info" v-if="activePhaseIndex < phases.length && !allPhasesComplete" v-on:click="advance">Advance Phase <i class="fa fa-forward"></i></button>

			<button style="margin-left:20px" v-if="editable" class="btn btn-default pull-right" v-on:click="editPhases"><i class="fa fa-pencil"></i> Edit Phases</button>

		</div>
						
		<h2>Phases</h2>

		<ul class="phase-list">
			<li v-for="(phase,index) in phases" 
				:class="{'list-group-item-info': phase.active == 1,
					     'list-group-item-success' : phase.completed_at != null
					    }
				" class="list-group-item">

				<span class="pull-right" v-if="phase.completed_at != null">Completed at {{ formatDate(phase.completed_at,'long, minutes') }}</span>

				<span class="pull-right" v-else-if="phase.active == 1">In Progress</span>

				<span class="pull-right" v-else>Pending</span>

				<h3>
					<i class="fa" 
						:class="{'fa-check':phase.completed_at != null, 
								'fa-ellipsis-h' : phase.completed_at == null,
								'fa-play-circle' : phase.active == 1}">
					</i> 
					{{ "label" in phase? phase.label : phase.phases.label }}
				</h3>

				<div style="clear: both;"></div>

				<label v-on:click="">Notes <i class="fa fa-edit"></i></label>
				<textarea class="form-control" v-model="phase.notes" v-on:change="autoSaveNote(phase)" :readonly="(phase.completed_at != null)"></textarea>

			</li>
		</ul>

		<modal-dialog
            v-if="phaseDialog.visible" 
            title="Project Phases" 
            :modal-visible="phaseDialog.visible" 
            confirm-button-text="Save"
            button-class="btn-success"
            v-on:confirm="updateProjectPhases"
            v-on:closeModal="phaseDialog.visible = false">

            <div v-if="masterPhases.length > 0">
            
	            <p>Select the phases that this project should progress through:</p>

	            <div v-for="phase in masterPhases" :key="phase.id" class="checkbox">
	            	<label>
	            		<input type="checkbox" :value="phase.id" v-model="phaseDialog.includedPhases">
	            		{{ phase.label }}
	            	</label>
	            </div>

	            <p>Want to add more phases? Head over to the <router-link to="/phases">Master Phase List</router-link>.</p>
	        </div>
	        <div v-else>
	        	<p>Oops! You haven't created any phases yet. Go on over to the <router-link to="/phases">Master Phase List</router-link> to create some!</p>
	        </div>

        </modal-dialog>
	</div>

</template>

<script type="text/javascript">
	import ModalDialog from '../components/ModalDialog'
	import bus from '../bus.js'

	export default {
		name: "PhaseList",
		props: ["phases","editable","ownerType"],
		components: {
			ModalDialog
		},
		data() {
			return {
				phaseDialog: {
                    visible: false,
                    includedPhases: []
                },
                masterPhases: []
			}
		},
		computed: {
			includedPhasesList() {
				var included = []

				this.phases.forEach(function(phase,index){
					included.push(phase.phase_id)
				})

				return included
			},
			activePhaseIndex() {
				let phase_index = 0;
				this.phases.forEach(function(phase,index) {
					if (phase.active == 1)
						phase_index = index
				})

				return phase_index
			},
			allPhasesComplete() {
				let phasesComplete = true

				this.phases.forEach(function(phase,index) {
					if (phase.completed_at == null)
						phasesComplete = false
				})

				return phasesComplete

			}
		},
		methods: {
			editPhases() {
				this.phaseDialog.visible = true
				this.phaseDialog.includedPhases = this.includedPhasesList
			},
			autoSaveNote(phase) {
				let vm = this

				let payload = {
					id: phase.id,
					notes: phase.notes
				}

				this.postData(window.apiBase+"ownerPhase/save",payload).then(function(response){

					bus.$emit("updateAlert",{
						class: "alert-success",
						msg: "Phase notes saved",
						icon: "fa-save",
						visible: true
					})
				
				})
			},
			updateProjectPhases() {
				let vm = this

				let payload = {
					phases: JSON.stringify(this.phaseDialog.includedPhases),
					owner_id: this.$route.params.id,
					type: this.ownerType
				}

				this.postData(window.apiBase+"phase/save-phase-list",payload).then(function(response){
					vm.phaseDialog.visible = false

					vm.$emit("phasesChanged")
				})
			},
			rollback() {
				let vm = this

				let payload = {
					owner_id : this.$route.params.id,
					owner_type: this.ownerType
				}

				this.postData(window.apiBase + "phase/rollback-active",payload).then(function(response){
					vm.$emit("phasesChanged");
				})
			},
			advance() {
				let vm = this

				let payload = {
					owner_id : this.$route.params.id,
					owner_type: this.ownerType
				}

				this.postData(window.apiBase + "phase/advance-active",payload).then(function(response){
					vm.$emit("phasesChanged");
				})
			},
			fetchMasterPhaseList() {
				let vm = this

				this.getJSON(window.apiBase+"phase/get").then(function(response){
					vm.masterPhases = response
				})
			}
		},
		created() {
			this.fetchMasterPhaseList()
		}
	}
</script>

<style type="text/css" scoped>

.phase-list {
	padding: 0;
	margin-top: 20px;
}
</style>