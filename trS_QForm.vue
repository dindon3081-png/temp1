<template>
	<LayoutQForm2 :listQuery.sync="listQuery">
	<template v-slot:SlotForm>
	<el-popover placement="bottom-start" width="330" trigger="click" :tabindex="-1">
		<div class="SetGroup">
			<div class="DrawerItem">
				<span class="item-label">檔案</span>
				<!-- {{ showPathDay }} -->
				<span class="fuchsia b NumF">{{ showPathDay }}</span>
			</div>
			<div class="DrawerItem">
				<!-- 連動選單 -->
				<div class="pathSelects1">
					<el-select placeholder="週別"
						v-model="activeWeek"
						@change="selPathWeek">
						<el-option-group
							v-for="group in arrPathMenus"
							:key="group.mon"
							:label="group.label">
							<el-option :key="week.value"
								v-for="week in group.options"
								:label="week.label"
								:value="week.value">
							</el-option>
						</el-option-group>
					</el-select>
					<el-select placeholder="日期" class="mt2"
						v-model="activeFile" filterable
						@change="selPathFile">
						<el-option :key="file.fname"
							v-for="file in arrDateFiles"
							:label="file.date"
							:value="file.fname">
						</el-option>
					</el-select>
				</div>
				<div v-if="!arrPathMenus.length" class="flex b red">無檔案</div>
			</div>
		</div>
		<el-button slot="reference" id="BtnFolder" class="mr1 bg_month white" icon="el-icon-folder-opened" circle></el-button>
	</el-popover>
	
	<el-dropdown trigger="click" class="ml2">
			<el-button plain>
				{{ showRate }}
				<i class="el-icon-caret-bottom el-icon--right" />
			</el-button>
			<el-dropdown-menu slot="dropdown">
				<div class="pnlRate ml3 mb3 pr4">
					<div class="rowRate">
						<el-switch class="mr3"
							v-model="listQuery.rateEnabled"
							active-text="手調"
							inactive-text="制定">
						</el-switch>
						<el-slider class="rateSlider mr3"
								v-model.number="listQuery.rateSlider"
								@change="chgSlider"
								:min="0"
								:max="300">
							</el-slider>
						<el-form-item>
							<s @click.right="inpRDel">
							<el-input type="number"
							class="rateInput"
							v-model.number="listQuery.rateInput"
							@change="chgIptRate"
							@focus="selAll" clearable>
								<template slot="prepend">自訂</template>
							</el-input>
							</s>
						</el-form-item>
					</div>
				</div>

				<div class="rowChk">
					<CheckBox class="mr2" v-model="listQuery.chkPers"
						v-for="item in arrChkList" :key="item.id"
						:label="item.name" :value="item.id" />
				</div>
			</el-dropdown-menu>
		</el-dropdown>

		<el-dropdown trigger="click" class="ml2">
			<el-button plain>
				欄位範圍
				<i class="el-icon-caret-bottom el-icon--right" />
			</el-button>
			<el-dropdown-menu slot="dropdown" class="no-padding w520">
				<div class="pnlRange">
					<div class="rangeRow" v-for="item in arrRanges" :key="item.key">
						<span class="rangeTitle">{{ item.name }}</span>
						<el-form-item>
							<s @click.right="inpRDel">
								<el-input type="number" v-model.number="listQuery[item.min]" @focus="selAll" clearable>
									<template slot="prepend">低</template>
								</el-input>
							</s>
						</el-form-item>
						<el-form-item>
							<s @click.right="inpRDel">
								<el-input type="number" v-model.number="listQuery[item.max]" @focus="selAll" clearable>
									<template slot="prepend">高</template>
								</el-input>
							</s>
						</el-form-item>
					</div>
				</div>
			</el-dropdown-menu>
		</el-dropdown>

		<el-button class="ml2" :size="getSize()" type="danger" @click="clickStop">中斷</el-button>
		<el-button class="ml2" :size="getSize()"type="info" @click="resetFilter">重置</el-button>
	</template>
	</LayoutQForm2>
</template>

<style lang="scss" scoped>
#BtnFolder {
	position: absolute;
	left: 8px;
	top: 8px;
}

.rowChk {
	margin: 0 1rem;
}

.pnlRate {
	width: 600px;
	max-width: calc(100vw - 10px);
}

.rowRate {
	display: flex;
	align-items: center;
}

.rateSlider {
	flex: 1;
	width: 200px;
}

.rateInput {
	width: 170px;
}

.pnlRange {
	padding: 12px;
	width: 520px;
	max-width: calc(100vw - 10px);
}

.rangeRow {
	display: grid;
	grid-template-columns: 70px minmax(120px, 1fr) minmax(120px, 1fr);
	column-gap: 10px;
	align-items: center;
}

.rangeTitle {
	font-weight: 700;
	white-space: nowrap;
}

@media (max-width: 450px) {
	.rowRate {
		display: grid;
		grid-template-columns: auto minmax(135px, 1fr);
		grid-template-areas:
			"switch input"
			"slider slider";
		column-gap: 12px;
		row-gap: 8px;
		align-items: center;
	}

	.rateSlider {
		grid-area: slider;
		width: 100%;
		margin-right: 0;
		padding: 0 4px;
	}

	.rangeRow {
		grid-template-columns: 1fr;
		row-gap: 4px;
	}
}
</style>

<script src="./trS_QFor_.js"></script>
