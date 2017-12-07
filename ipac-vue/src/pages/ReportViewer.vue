<template>

	<div class="report-print">
		
		<section class="cover-page">
			<div class="details">
				<h1 class="report-title">{{ report.report_title }}</h1>
				<div class="report-date">{{ formattedDate }}</div>
			</div>

			<img src="/static/cover_page.jpg" class="cover-page-graphic">
		</section>

		<section class="page preface-page" v-html="renderedPreface"></section>

		<section class="toc page">
			<div class="header">
				<img src="/static/logo.png" class="logo">
			</div>

			<h2>Table of Contents</h2>
			<ul>
				<li v-for="(section,index) in sections">
					{{ section.heading }}
					<div class="pull-right">{{ index+3 }}</div>
				</li>
			</ul>

			<img src="/static/report_footer.png" class="footer-graphic">
		</section>

		<div v-for="(section,index) in renderedSections">
			<section class="report-section page" >
				<div class="header">
					<img src="/static/logo.png" class="logo">
				</div>

				<h2>{{ section.heading }}</h2>
				<div class="intro" v-html="section.description_text"></div>
				<div v-if="section.has_guidelines == '1'">
					<h3>Guidelines And Requirements</h3>

					<div v-html="section.guidelines_text"></div>
				</div>
				<div v-if="section.has_findings == '1'">
					<h3>Findings</h3>

					<div v-html="section.findings"></div>
				</div>

				<div class="page-num">{{ index+3 }}</div>
				<img src="/static/report_footer.png" class="footer-graphic">
			</section>

			<!--<section class="report-section page overflow-page" >
				<div class="header">
					<img src="/static/logo.png" class="logo">
				</div>

				<div v-html="section.findings[1]"></div>

				<div class="page-num">{{ index+3 }}</div>
				<img src="/static/report_footer.png" class="footer-graphic">
			</section>-->
		</div>

	</div>

</template>

<script type="text/javascript">

	export default {
		name: "ReportViewer",
		props: [],
		components: {
			
		},
		data () {
			return {
				sections: [],
				report: {
					report_title: "",
					preface_text: "",
					date_issued: "",
					client: {}
				},
				variables: {
					client: {},
					company: {},
					location: {},
					report_date: ""
				},
				company: {},
				pdfUrl: window.apiBase+"report/pdf",
				pageHtml: "",
				apiKey: localStorage.apiKey
			}
		},
		computed: {
			formattedDate() {
				let d = new Date(this.report.date_issued)

				var monthNames = [
				    "January", "February", "March",
				    "April", "May", "June", "July",
				    "August", "September", "October",
				    "November", "December"
				];

				var day = d.getDate();
				var monthIndex = d.getMonth();
				var year = d.getFullYear();

				return day + ' ' + monthNames[monthIndex] + ' ' + year;
			},
			/*variables() {
				return {
					client: this.report.client,
					company: this.company,
					report_date: this.report.date_issued,
				}
			},*/
			renderedPreface() {
				let renderedPreface = ""

				if (this.report.preface_text)
					renderedPreface = Mustache.render(this.report.preface_text, this.variables)
				
				return renderedPreface
			},
			renderedSections() {
				let renderedSections = [];
				let vm = this

				//make sure client object exists, otherwise skip render (it will render again when client changes)
				if (Object.keys(this.variables.client).length > 0 && Object.keys(this.variables.company).length > 0 && this.variables.report_date) {

					this.sections.forEach(function(section, index) {

						if (typeof section.description_text == "string")
							section.description_text = Mustache.render(section.description_text, vm.variables)
						
						if (typeof section.guidelines_text == "string")
							section.guidelines_text = Mustache.render(section.guidelines_text, vm.variables)
						
						if (typeof section.findings == "string") {
							section.findings = Mustache.render(section.findings, vm.variables)
						}
						
						if (typeof section.heading == "string")
							section.heading =  Mustache.render(section.heading, vm.variables)

						renderedSections.push(section)
					})

					//console.log("It should all be rendered now")
				}
				else
					//console.log("Skipping template render...")

				return renderedSections
			}
		},
		methods: {
			fetchReport() {
				let vm = this

				this.getJSON(window.apiBase + "report/find/"+this.$route.params.id).then(function(response){

					vm.report.client = response.client
					vm.sections = response.sections
					vm.report.preface_text = response.preface_text
					vm.report.date_issued = response.date_issued
					vm.report.report_title = response.report_title

					vm.variables.client = response.client
					vm.variables.report_date = response.date_issued
					vm.variables.location = response.location

					vm.$emit("toggleSpinner",false)

				})				
			},
			fetchCompany() {
				let vm = this

				this.getJSON(window.apiBase + "setting/get").then(function(response){

					response.forEach(function(setting, index) {

						//company template variables
						vm.company[setting.setting] = setting.value


					})

					vm.variables.company = vm.company
				})
			}
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.fetchReport();
			this.fetchCompany()

			let vm =this
			setTimeout(function(){
				console.log(vm.pageHtml)
				vm.pageHtml = "<link href='https://ipac-api.chadtiffin.com/assets/pdf.css'>"
				vm.pageHtml += document.querySelector(".report-print").outerHTML
			},3000)
		},
	}
</script>

<style type="text/css">
	@media print {
	    header {
	      display: none!important;

	    }

	    .report-print, .section-wrapper, .dashboard, #app {
	    	margin: 0;
	    }

	    section.cover-page, section {
	      margin: 0!important;
	      margin-top: 0!important;
	      box-shadow: none;
	      min-height: 11in;
	    }

	    html, body {
	      margin: 0;
	    }
	  }
</style>

<style type="text/css" scoped>

	section {
		page-break-after: always;
		box-shadow: 0 0 10px black;
		padding: 80px;
		padding-top: 30px;
		width: 8.5in;
		min-height: 10.97in;
		position: relative;
		font-size: 12px;
	}

	section.cover-page {
		margin-top: 30px;
	}

	section.preface-page {
		padding-top: 100px;
	}

	.cover-page .details {
		position: relative;
		z-index: 2;
		color: #D8D1BF;
		margin-top: 250px;
	}

	.details h1 {
		font-variant: small-caps;
		font-size: 22pt;
		font-weight: bold;
		margin-bottom: 50px;
	}

	.details .report-date {
		font-size: 14pt;
	}

	.page h2, .page h3 {
		color: #3B544C;
		font-variant: small-caps;
		text-align: center;
	}

	.page h3 {
		font-size: 16pt;
		font-weight: lighter;
		border-bottom: 1px dotted #3B544C;
		text-align: left;
	}

	.toc ul {
		list-style: none;
		padding: 0;
		margin-top: 20px;
	}

	.toc li {
		border-bottom: 1px dotted #a0a0a0;
		margin-bottom: 20px;
		display: block;
	}

	.page-num {
		position: absolute;
		bottom: 25px;
		left: 0;
		right: 0;
		z-index: 1;
		text-align: center;
	}

	/*.report-section {
		background-image: url("/static/report_footer.png");
		background-size: contain;
		background-position: bottom center;
		background-repeat: no-repeat;
	}*/

	.page .header {
		border-bottom: 2px solid #3B544C;
		margin-bottom: 10px;
	}

	.page .logo {
		width: 200px;
	}

	.cover-page-graphic {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		z-index: 0;
	}

	.page .footer-graphic {
		position: absolute;
		left: 0;
		bottom: 0;
		width: 100%;
	}

</style>

