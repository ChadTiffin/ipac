<template>
	<section>
		<div class="button-bar">
			<button v-on:click="newProject" class="btn btn-success"><i class="fa fa-plus"></i> Project</button>
		</div>

		<div class="row">
			<div class="col-md-3">

				<div class="well">
					<h3>Filter Projects</h3>

					<button-group
						vertical="true" 
						:buttons="projectFilter.buttons" 
						:full-width="true"
						v-on:change="getProjects"
						v-model="projectFilter.value">
					</button-group>
				</div>

			</div>
			<div class="col-md-9">

				<h2>Projects</h2>

				<router-link v-for="project in mutatedProjects" :key="project.id" :to="'/projects/'+project.id+'/tasks'" class="project-link">
					<div class="panel panel-default project">
						<div class="panel-body">
							<div class="row">
								<div class="col-md-4">
									<div class="well">
									<table class="table">
										<tbody>
											<tr>
												<th>Status:</th>
												<td>{{ project.archived == 1 ? 'Archived' : 'Active' }}</td>
											</tr>
											<tr>
												<th>Current Phase:</th>
												<td>{{ project.currentPhase }}</td>
											</tr>
											<tr>
												<th>Date Created:</th>
												<td>{{ project.created_at }}</td>
											</tr>
											<tr v-if="project.project_lead_id != 0">
												<th>Project Lead:</th>
												<td>{{ project.users ? project.users.first_name : '' }} {{ project.users ? project.users.last_name : '' }}</td>
											</tr>
											<!--<tr v-if="project.project_lead_id != 0">
												<th>Project Lead:</th>
												<td>{{ project.project_lead_user.first_name }} {{ project.project_lead_user.last_name }}</td>
											</tr>-->
											<tr v-if="project.phases">
												<th>Current Phase:</th>
												<td>{{ project.currentPhase }}</td>
											</tr>
										</tbody>
									</table>
									</div>
								</div>
								<div class="col-md-8">
									<h3>{{ project.project_name }}</h3>
									<p>{{ project.description }}</p>
								</div>
							</div>
						</div>
					</div>
				</router-link>
			</div>
		</div>

		<modal-dialog
            v-if="projectDialog.visible" 
            :title="projectDialog.title" 
            :modal-visible="projectDialog.visible" 
            confirm-button-text="Save"
            button-class="btn-success"
            v-on:confirm="saveProject"
            v-on:closeModal="projectDialog.visible = false">
            
            <form-group label="Project Name" col-class="col-md-4">
            	<input type="text" class="form-control" v-model="projectDialog.values.project_name">
            </form-group>

            <form-group label="Project Description" col-class="col-md-4">
            	<textarea class="form-control" v-model="projectDialog.values.description"></textarea>
            </form-group>

            <form-group label="Project Lead" col-class="col-md-4">
            	<select class="form-control" v-model="projectDialog.values.project_lead_id">
            		<option v-for="user in $root.users" :value="user.id">{{ user.first_name }} {{ user.last_name }}</option>
            	</select>
            </form-group>

        </modal-dialog>

	</section>

</template>

<script type="text/javascript">
	import FormGroup from '../components/FormGroup'
	import ModalDialog from '../components/ModalDialog'
	import DateField from '../components/DateField'
	import ButtonGroup from '../components/ButtonGroup'

	export default {
		name: "Projects",
		components: {
			FormGroup,
			ModalDialog,
			DateField,
			ButtonGroup
		},
		data () {
			return {
				projects: [],
				projectDialog: {
					visible: false,
					title: "New Project",
					values: {}
				},
				projectFilter: {
                	value: "Active",
                	buttons: [
                		{
                			text: "Active",
                			value: "Active"
                		},
                		{
                			text: "Archived",
                			value: "Archived"
                		}
                	]
                }
			}
		},
		computed: {
			mutatedProjects() {
				let projects = this.projects

				let mutated = []

				if (projects) {

					projects.forEach(function(project, index){
						project.created_at = moment(project.created_at).format("MMMM Do YYYY")

						let filteredPhases = []

						if (project.owner_phases) {

							project.owner_phases.forEach(function(phase, index){
								if (phase.owner_type == "project") {
									filteredPhases.push(phase)

									if (phase.active == 1)
										project['currentPhase'] = phase.phases.label
								}
							})

							project.owner_phases = filteredPhases
						
							let lowest_phase = 10000;
							project.owner_phases.forEach(function(phase, index){
								if (phase.completed_at == null) {
									if (phase.priority < lowest_phase) {
										lowest_phase = phase.priority
										project.currentPhase = phase.title
									}
								}
							})
						}

						mutated.push(project)
					})
				}

				return mutated
			}
		},
		methods: {
			newProject() {
				this.projectDialog.values = {
					project_name: "",
					description: "",
					project_lead_id: 0,
					created_by: "{{{current_user}}}",
					created_at: "{{{server_now}}}"
				}
				this.projectDialog.visible = true
			},
			saveProject() {

				let vm = this

				this.postData(window.apiBase+"project/save",this.projectDialog.values).then(function(response) {
					vm.projectDialog.values = {}
					vm.projectDialog.visible = false
					vm.getProjects()
				})
			},
			getProjects() {
				//default filter
				let filters = [
					["archived",0]
				]

				if (this.projectFilter.value == "Archived") {
					filters = [
						["archived",1]
					]
				}

				this.fetchProjects(filters,["created_at","DESC"])
			}
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.getProjects()
		}
	}
</script>

<style type="text/css" scoped>

pre {
	font-family: inherit;
	background-color: transparent;
	border: none;
	padding: 0;
}

.well .table td, .well .table th {
	border: none;
}

.well {
	margin-bottom: 0;
}

.project-link {
	text-decoration: none;
	color: inherit;
}

.project-link:hover .project {
	box-shadow: 0 0 5px rgba(1,1,1,0.4);
	cursor: pointer;
	background-color: #f5fdff;
}

</style>

