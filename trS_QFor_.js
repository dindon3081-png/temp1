import QForm from '@/components/mixin/QForm'
import LayoutQForm2 from '@/components/LayoutQForm2'
import CheckBox from "@/components/CheckBox.vue"
import { AjaxAct } from '@/utils/ajax1'
import { GetNow,GetThatday,GetThatMon,GetWeekOfMonth } from '@/fun1.js'

export default {
  mixins: [QForm],
	props: ['listQuery','frmOpt','flgB1'],
  components: { LayoutQForm2,CheckBox },
  data() {
		return {
		  arrChks: [
				{ id: 1, name: '只看持有' },
				{ id: 2, name: '損益率高的' },
				{ id: 3, name: '損益率低的' },
		  ],
			arrRanges: [
				{ key: 'price1', name: '現價', min: 'price11', max: 'price12' },
				{ key: 'remain', name: '今餘', min: 'remain1', max: 'remain2' },
				{ key: 'cost1', name: '成本', min: 'cost1', max: 'cost2' },
				{ key: 'payMon', name: '付出成本', min: 'payCost1', max: 'payCost2' },
			],
			isCollapse: false,
			activeFile: '',
			activeWeek: '',
			curMon: '',
			arrPathMenus: [],
		}
  },
	computed: {
		arrChkList() {
			return this.frmOpt.arrChks || this.arrChks
		},
		showRate() {
			const {rateEnabled, rateInput, rateSlider} = this.listQuery
				,rate1 = rateEnabled ? (rateSlider || 0) : (rateInput || 0)
				,txtMode = rateEnabled ? '手調' : '制定'
				,title = this.frmOpt.rateTitle || '損益率'
			return `${title} ${txtMode}: ${rate1}%`
		},
		pathTitle() {
			return this.curMon ? `${this.curMon.left(4)}-${this.curMon.substr(4, 2)}` : ''
		},
		showPathDay() {
			return this.activeFile ? this.activeFile.replace('.json', '') : (this.pathTitle || '檔案日期')
		},
		arrDateFiles() {
			const week = findWeekByValue(this.arrPathMenus, this.activeWeek)
			return week ? week.files : []
		}
	},
  methods: {
		getPathMenu() {
			const day1 	= GetThatday(GetNow('d2'), -1, 1)	// 抓前一個工作天
				,arrMon 	= [0, -1, -2].map(n => GetThatMon(day1, n).left(6))
			this.curMon = arrMon[0]

			AjaxAct('Get_AllPathFile', {fpath: 'TradeSino', path: arrMon}).then(res => {
				// console.log('getPathMenu-res: ', JSON.stringify(res));	// @@
				this.arrPathMenus = getMenuMonths(res, arrMon)
				this.setLastPath()
			})
		},
		setLastPath() {
			const arrFiles = []
			this.arrPathMenus.forEach(mon => {
				mon.options.forEach(week => {
					week.files.forEach(file => arrFiles.push({ mon, week, file }))
				})
			})
			if (!arrFiles.length) { return }

			arrFiles.sort((a, b) => a.file.date > b.file.date ? 1 : -1)
			const last = arrFiles[arrFiles.length - 1]
			this.activeWeek = last.week.value
			this.activeFile = last.file.fname
			this.emitPathFile(last.file.fname)
		},
		selPathWeek(value) {
			const week = findWeekByValue(this.arrPathMenus, value)
				,files = week ? week.files : []
			if (!files.length) { return }

			const last = files[files.length - 1]
			this.activeFile = last.fname
			this.emitPathFile(last.fname)
		},
		selPathFile(fname) {
			this.emitPathFile(fname)
		},
		emitPathFile(fname) {
			this.$emit('chgPathFile', fname)
		},
		chgIptRate(v1) {
			this.listQuery.rateEnabled = !parseInt(v1)
		},
		chgSlider(v1) {
			this.listQuery.rateEnabled = !!parseInt(v1)
		},
		getSize() {
			return !this.isMobile ? '' : 'mini'
		},
		clickStop() {
			console.warn('clickStop')	// @@
      this.$emit('doStop')
		},
  },
	mounted() {
		this.getPathMenu()
	},
}

function getMenuMonths(arrPath=[], arrMons) {
	const arrFiles = parsePathFiles(arrPath, arrMons)
	return arrMons.map(mon => {
		const files = arrFiles.filter(v => v.mon == mon)
			,weeks = groupWeeks(files, mon)
		return {
			mon: mon,
			label: `${mon.left(4)}-${mon.substr(4, 2)}`,
			options: weeks,
		}
	}).filter(group => group.options.length)
}

function groupWeeks(files, mon) {
	let weeks = []
	files.forEach(file => {
		let week = weeks.find(v => v.week == file.week)
		if (!week) {
			week = {
				week: file.week,
				value: `${mon}-${file.week}`,
				label: `第${file.week}週`,
				files: [],
			}
			weeks.push(week)
		}
		week.files.push(file)
	})
	weeks.forEach(week => week.files.sort((a, b) => a.date > b.date ? 1 : -1))
	weeks.sort((a, b) => a.week - b.week)
	return weeks
}

function findWeekByValue(arrMenus, value) {
	for (const mon of arrMenus) {
		const week = mon.options.find(v => v.value == value)
		if (week) { return week }
	}
	return null
}

function parsePathFiles(arrPath, arrMons) {
	let arr1 = []
		,seen = {}
	arrPath.forEach(row => {
		if (row && row.dir && Array.isArray(row.files)) {
			row.files.forEach(file => addFile(arr1, seen, String(file)))
			return
		}

		const txt = typeof row == 'string' ? row : JSON.stringify(row)
			,hit = txt.match(/\d{8}(?=\.json|["\\\/]|$)/g)
	console.warn('parsePathFiles-hit: ', hit)	// @@	
	if (hit) {
			hit.forEach(date1 => addFile(arr1, seen, date1))
		}
	})
	arr1 = arr1.filter(v => !arrMons || arrMons.includes(v.mon))
	arr1.sort((a, b) => a.date > b.date ? 1 : -1)
	return arr1
}

function addFile(arr1, seen, date1) {
	date1 = date1.replace('.json', '')
	const mon = date1.left(6)
	if (seen[date1]) { return }
	seen[date1] = true
	arr1.push({
		mon: mon,
		date: date1,
		fname: `${date1}.json`,
		week: GetWeekOfMonth(date1),
	})
	console.warn('addFile-arr1: ', arr1)	// @@	
}
