<template>

	<div class="control">
		<div class="total">
			{{ compliance }}/{{ missed }} = {{ score }}
		</div>

		<div class="counter-btn success">
			<button type="button" v-on:click="compliance++" class="btn-success plus">
				<i class="fa fa-plus"></i><br>
				Compliance
			</button>
			<button type="button" v-on:click="complianceMinus" class="btn-danger minus">
				<i class="fa fa-minus"></i>
			</button>
		</div>

		<div class="counter-btn primary">
			<button type="button" v-on:click="missed++" class="btn-primary plus">
				<i class="fa fa-plus"></i><br>
				Missed Opportunity
			</button>
			<button type="button" v-on:click="missedMinus" class="btn-danger minus"><i class="fa fa-minus"></i></button>
		</div>
	</div>

</template>

<script type="text/javascript">

	export default {
		name: "OpportunityCounterField",
		props: ["value"],
		data() {
			return {
				activeIndex: null,
				compliance: 0,
				missed: 0
			}
		},
		watch: {
			value() {
				this.compliance = this.value[0]
				this.missed = this.value[1]
			},
			missed() {
				this.$emit("input",[this.compliance, this.missed])
				this.$emit("change",[this.compliance, this.missed])
			},
			compliance() {
				this.$emit("input",[this.compliance, this.missed])
				this.$emit("change",[this.compliance, this.missed])
			}
		},
		computed: {
			score() {
				let score = Math.round((this.compliance / (this.compliance + this.missed)) * 100)
				if (isNaN(score))
					return "?"
				else 
					return score+"%"
			}
		},
		methods: {
			complianceMinus() {
				if (this.compliance > 0)
					this.compliance--
			},
			missedMinus() {
				if (this.missed > 0)
					this.missed--

			}
		},
		created() {

			if (this.value) {
				this.compliance = this.value[0]
				this.missed = this.value[1]
			}
			else {
				this.compliance = 0
				this.missed = 0
			}
		}
	}
</script>

<style type="text/css" scoped>
	.control {
		margin-bottom: 10px;
	}

	.counter-btn {
		display: inline-block;
		width: calc(50% - 4px);
	}

	.plus, .minus {
		display: block;
		width: 100%;
		border: none;
	}

	.plus {
		padding: 5px;
		font-size: 14pt;
		height: 110px;
		border-top-left-radius: 20px; 
		border-top-right-radius: 20px; 
	}

	.minus {
		font-size: 12px;
		border-bottom-left-radius: 20px; 
		border-bottom-right-radius: 20px; 
		padding: 5px;
	}

	.total { 
		font-size: 18pt;
		text-align: center;
		color: #606060;
	}
</style>