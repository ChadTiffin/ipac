<template><div>
	<section v-if="!$root.isOffline">
		<div class="button-bar">

			<router-link v-if="activeTab == 'section-templates'" class="btn btn-success" to="/templates/sections/edit/new"><i class="fa fa-plus"></i> Section Template</router-link>

			<router-link v-if="activeTab == 'report-templates'" to="/templates/reports/edit/new" class="btn btn-success"><i class="fa fa-plus"></i> Report Template</router-link>

			<nav-tabs classes="pull-right" v-if="$route.params.id != 'new'">
				<li :class="{active: activeTab == 'report-templates'}"><a href="#" v-on:click.prevent="activeTab = 'report-templates'">
					<i class="fa fa-address-card-o"></i>
					Report Templates
				</a></li>

				<li :class="{active: activeTab == 'section-templates'}"><a href="#" v-on:click.prevent="activeTab = 'section-templates'">
					<i class="fa fa-map-marker"></i>
					Section Templates
				</a></li>
			</nav-tabs>
		</div>

		<div class="tab-content">

			<div v-if="activeTab == 'report-templates'">
				<report-templates></report-templates>
			</div>
			<div v-else-if="activeTab == 'section-templates'">
				<section-templates></section-templates>
			</div>
		</div>

	</section>
	<page-offline-alert v-else></page-offline-alert>

</div></template>

<script type="text/javascript">
	import SectionTemplates from "./SectionTemplates"
	import ReportTemplates from "./ReportTemplates"
	import PageOfflineAlert from '../components/PageOfflineAlert'
	import TableList from '../components/TableList'
	import NavTabs from '../components/NavTabs'

	export default {
		name: "Templating",
		props: [],
		components: {
			TableList,
			PageOfflineAlert,
			SectionTemplates,
			ReportTemplates,
			NavTabs
		},
		data () {
			return {
				activeTab: 'report-templates'
			}
		},
		methods: {
			fetchTemplates() {
				let vm = this

				this.getJSON(window.apiBase + "reportTemplate/get").then(function(response){

					if ("status" in response && response.status == "offline") {

					}
					else {
						vm.templates = response
					}

					vm.$emit("toggleSpinner",false)
				})
			},
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.fetchTemplates();

			//set active tab
			let segments = this.$route.path.split("/")

			if (segments[2] == "sections")
				this.activeTab = "section-templates"
			else
				this.activeTab = "report-templates"
		}
	}
</script>

<style type="text/css" scoped>



</style>

