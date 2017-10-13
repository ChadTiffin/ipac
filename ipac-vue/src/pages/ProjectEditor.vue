<template>
	<section>

		<div class="button-bar">
			<router-link to="/projects" class='router-link'><i class="fa fa-angle-double-left"></i> Back to Projects</router-link>

			<nav-tabs classes="pull-right" v-if="$route.params.id != 'new'" :tabs="navTabs"></nav-tabs>

			<div style="clear: both;"></div>
			
		</div>

		<h1 v-if="project">{{ project.project_name }}</h1>
		<hr>

		<div class="row" v-if="project">
			<div class="col-md-4">
				<div class="well project-details">
					<button v-on:click="editProjectDetails" v-if="project.archived == 0" style="position: absolute;top: 5px;right: 5px;" class="btn btn-default btn-sm"><i class="fa fa-pencil"></i></button>

					<div class="alert alert-warning" v-if="project.archived == 1">
						This project cannot be edited because it is archived.
					</div>

					<table class="table" style="margin-bottom: 20px;">
						<tbody v-if="project">
							<tr>
								<th>Status:</th>
								<td>{{ projectStatus }}</td>
							</tr>
							<tr>
								<th>Current Phase:</th>
								<td>{{ currentPhase }}</td>
							</tr>
							<tr>
								<th>Description:</th>
								<td>{{project.description}}</td>
							</tr>
							<tr>
								<th>Created By:</th>
								<td>{{project.created_by_user.first_name}} {{project.created_by_user.last_name}}</td>
							</tr>
							<tr>
								<th>Date Created:</th>
								<td>{{ createdAt }}</td>
							</tr>
							<tr v-if="project.project_lead_user">
								<th>Project Lead:</th>
								<td>{{project.project_lead_user.first_name}} {{project.project_lead_user.last_name}}</td>
							</tr>
						</tbody>
					</table>

					<button v-if="project.archived == 0" class="btn btn-warning block-button" v-on:click="archiveProject"><i class="fa fa-archive"></i> Archive Project</button>

					<button v-else class="btn btn-info block-button" v-on:click="archiveProject"><i class="fa fa-archive"></i> Un-Archive Project</button>

					<button class="btn btn-danger block-button" v-on:click="deleteProject"><i class="fa fa-times"></i> Delete Project</button>

					<div style="clear: both;"></div>
				</div>

				<div v-if="activeTab == 'tasks'" class="well">
					<h2>Filter Tasks</h2>

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

					<div v-if="activeTab == 'tasks'">

						<task-list :heading="taskFilter.value + ' Tasks'" owner-type="project" :include-new-button="true" :tasks="filteredTasks" :editable="project.archived == 0" v-on:modelChanged="fetchProject"></task-list>

					</div>
					<div v-if="activeTab == 'phases'">

						<phase-list :phases="project.phases" :editable="project.archived == 0" owner-type="project" v-on:phasesChanged="fetchProject"></phase-list>

					</div>
					<div v-if="activeTab == 'expenses'">

						<h2>Project Expenses for {{ $root.userFullName }}</h2>

						<expense-list owner-type="project"></expense-list>

					</div>

				</div>
			</div>

		</div>

		<div v-else-if="projectNotFound">
			Project not found
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
	import NavTabs from '../components/NavTabs'
	import TaskList from '../components/TaskList'
	import ButtonGroup from '../components/ButtonGroup'
	import FormGroup from '../components/FormGroup'
	import ModalDialog from '../components/ModalDialog'
	import PhaseList from '../components/PhaseList'
	import ExpenseList from '../components/ExpenseList'

	export default {
		name: "ProjectEditor",
		components: {
			NavTabs,
			TaskList,
			ButtonGroup,
			ModalDialog,
			FormGroup,
			PhaseList,
			ExpenseList
		},
		data () {
			return {
				projectNotFound: false,
				project: null,
				activeTab: this.$route.params.tab,
				projectDialog: {
					visible: false,
					title: "Edit Project Details",
					values: {}
				},
				confirmDialog: {
                    visible: false,
                    title: "",
                    buttonText: "Delete",
                    buttonClass: "btn-danger",
                    parameters: "",
                    successFunction: ""
                },
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
                navTabs: [
                	
                	{
                		label: "Phases",
                		path: "phases",
            			icon: "fa-forward"
                	},
                	{
                		label: "Tasks",
                		path: "tasks",
            			icon: "fa-check-square-o",
            			bubble: 0
                	},
                	{
                		label: "Expenses",
                		path: "expenses",
            			icon: "fa-money"
                	}
                ]
			}
		},
		watch: {
			'$route': function(to, from) {
				this.activeTab = to.params.tab
			}
		},
		computed: { 
			createdAt() {
				return moment(this.created_at).format("MMMM Do YYYY")
			},
			userFullName() {
				if ("userDetails" in localStorage) {
					let user = JSON.parse(localStorage.userDetails)

					return user.first_name + " " + user.last_name
				}
				else
					return "You"
			},
			projectStatus() {
				if (this.project.deleted == 1)
					return "Deleted"
				else {
					if (this.project.archived == 1)
						return "Archived"
					else 
						return "Active"
				}
			},
			currentPhase() {
				let currentPhase = "";

				this.project.phases.forEach(function(phase, index){
					if (phase.active == 1)
						currentPhase = phase.label
				})

				return currentPhase
			},
			filteredTasks() {
				let filtered = [] 

				let vm = this

				this.navTabs[1].bubble = 0
				this.project.tasks.forEach(function(task, index){
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

					if (task.is_complete == 0)
						vm.navTabs[1].bubble++
				})

				return filtered
				
			}
		},
		methods: {
			editProjectDetails() {
				this.projectDialog.values = {
					id: this.project.id,
					project_name: this.project.project_name,
					description: this.project.description,
					project_lead_id: this.project.project_lead_id
				}

				this.projectDialog.visible = true
			},
			saveProject() {
				let vm = this

				this.postData(window.apiBase+"project/save",this.projectDialog.values).then(function(response) {
					vm.projectDialog.values = {}
					vm.projectDialog.visible = false

					vm.fetchProject()
				})
			},
			deleteProject() {
				this.confirmDialog.title = "Delete project?"
                this.confirmDialog.message = "Are you sure you want to delete this entire project?"
                this.confirmDialog.successFunction = "executeDelete"
                this.confirmDialog.buttonText = "Delete"
                this.confirmDialog.buttonClass = "btn-danger"

                this.confirmDialog.visible = true
			},
			undelete() {
				let payload = {
					id: this.$route.params.id,
					deleted: 0
				}

				let vm = this
				this.postData(window.apiBase+"project/save",payload).then(function(response){
					vm.fetchProject()
				})
			},
			executeDelete() {
				let payload = {
					id: this.$route.params.id,
				}

				let vm = this

				this.postData(window.apiBase+"project/delete",payload).then(function(response){

					vm.confirmDialog.visible = false
					vm.$router.push("/projects")
				})
			},
			archiveProject() {

				let payload = {
					id: this.$route.params.id,
					archived: this.project.archived == 0 ? 1 : 0
				}

				let vm = this
				this.postData(window.apiBase+"project/save",payload).then(function(response){
					vm.fetchProject()
				})
			},
			fetchProject() {
				let vm = this

				this.getJSON(window.apiBase+"project/find/"+this.$route.params.id).then(function(response){

					if ('status' in response) {
						vm.projectNotFound = true
					}
					else {
						vm.project = response

						let pageTitle = {
							mainTitle: response.project_name,
							subTitle: "Project"
						}
						vm.$emit("pageTitle",pageTitle)

						vm.filteredTasks
					}
					vm.$emit("toggleSpinner",false)
				})
			},

		},
		created() {
			
			this.fetchUsers()
			this.fetchProject()
		}
	}
</script>

<style type="text/css" scoped>

.project-details td, .project-details th {
	border: none;
}

.well {
	position: relative;
}

</style>

