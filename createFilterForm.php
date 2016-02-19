<?php
if (in_array ($nUserId, $filter_for_group_of_requirements)){

	function getDictionaryForFilters($conn) {

		$result = array();

		$sql = 'begin
  				sr_workshop_ts.pflashwebselect_main(
  													p_currriskgradembki => :p_currriskgradembki,
													p_currki => :p_currki,
													p_curragentcode => :p_curragentcode,
													p_currhexcolor => :p_currhexcolor,
													p_currgroups => :p_currgroups
													);
				end;';

		$curs_currriskgradembki = oci_new_cursor($conn);
		$curs_currki = oci_new_cursor($conn);
		$curs_curragentcode = oci_new_cursor($conn);
		$curs_currhexcolor = oci_new_cursor($conn);
		$curs_currgroups = oci_new_cursor($conn);

		$stmt = oci_parse($conn, $sql);

		oci_bind_by_name($stmt, ':p_currriskgradembki', $curs_currriskgradembki, -1, SQLT_RSET);
		oci_bind_by_name($stmt, ':p_currki', $curs_currki, -1, SQLT_RSET);
		oci_bind_by_name($stmt, ':p_curragentcode', $curs_curragentcode, -1, SQLT_RSET);
		oci_bind_by_name($stmt, ':p_currhexcolor', $curs_currhexcolor, -1, SQLT_RSET);
		oci_bind_by_name($stmt, ':p_currgroups', $curs_currgroups, -1, SQLT_RSET);

		if (oci_execute($stmt, OCI_DEFAULT)) {

			oci_execute($curs_currriskgradembki);
			oci_execute($curs_currki);
			oci_execute($curs_curragentcode);
			oci_execute($curs_currhexcolor);
			oci_execute($curs_currgroups); //Разбираем курсоры которые внутри курсора

			oci_fetch_all($curs_currriskgradembki, $result['DICT']['currriskgradembki'], null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
			oci_fetch_all($curs_currki, $result['DICT']['currki'], null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
			oci_fetch_all($curs_curragentcode, $result['DICT']['curragentcode'], null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
			oci_fetch_all($curs_currhexcolor, $result['DICT']['currhexcolor'], null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);

			$count = 0;
			while ($row = oci_fetch_array($curs_currgroups, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC)) {
				$get_all_rows_name = array_keys($row);
				foreach($get_all_rows_name as $y => $value) {
					if(gettype($row[$value]) == "resource") {
						oci_execute($row[$value]);
						oci_fetch_all($row[$value], $result['GRUPS'][$count][$value], null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
					}
					else {
						$result['GRUPS'][$count][$value] = $row[$value];
					}
				}
				$count++;
			}

			$data = array('error' => 0, 'errtext' => 'Ok!', 'result' => $result);

			oci_free_statement($curs_currriskgradembki);
			oci_free_statement($curs_currki);
			oci_free_statement($curs_curragentcode);
			oci_free_statement($curs_currhexcolor);
			oci_free_statement($curs_currgroups);

		}
		else {
			$e = oci_error($stmt);
			$data = array('error' => 1, 'errtext' => $e['message']);
		}
		return json_encode($data);
	}

	?>
	<fieldset>
		<legend>Формирование групп заявок</legend>
		<div class="cssParamsConstractor">
			<form>
				<div>
					<label for="cons_group">Группа</label>
					<input class="paramsConstractor form-control" id="cons_group" type="text" required>
				</div>
				<div>
					<label for="cons_color">Цвет</label>
					<select class="paramsConstractor form-control" id="cons_color" required>
						<option>-</option>
					</select>
				</div>
				<div>
					<label for="cons_priority">Приоритет</label>
					<input class="paramsConstractor form-control" id="cons_priority" type="text" disabled="disabled" required>
				</div>
				<div>
					<label for="cons_ubki_min">Рейтинг УБКИ от</label>
					<input class="paramsConstractor form-control" id="cons_ubki_min" type="number" min="0">
					<label for="cons_ubki_max">до</label>
					<input class="paramsConstractor form-control" id="cons_ubki_max" type="number" min="0">
				</div>
				<div>
					<label for="cons_mbki" class="cssBotCons">МБКИ</label>
					<select class="paramsConstractorSel" id="cons_mbki" multiple="multiple" size="4"></select>
					<div class="selected"></div>
				</div>
				<div>
					<label for="cons_ki" class="cssBotCons">Кредитная история</label>
					<select class="paramsConstractorSel" id="cons_ki" multiple="multiple" size="4"></select>
					<div class="selected"></div>
				</div>
				<div>
					<label for="cons_sum_min">Сумма кредита от</label>
					<input class="paramsConstractor form-control" id="cons_sum_min" type="number" min="0">
					<label for="cons_sum_max">до</label>
					<input class="paramsConstractor form-control" id="cons_sum_max" type="number" min="0">
				</div>
				<div>
					<label for="cons_agent" class="cssBotCons">Агент</label>
					<select class="paramsConstractorSel" id="cons_agent" multiple="multiple" size="4"></select>
					<div class="selected"></div>
				</div>
			</form>
		</div>
		<div class="cssParamsTable">
			<table>
				<thead>
					<tr class="popup_table_head">
						<td>Название</td>
						<td>Приоритет - Цвет</td>
						<td>Дата</td>
						<td>Удалить</td>
					</tr>
				</thead>
				<tbody id="ready_filter"></tbody>
			</table>
		</div>
		<div class="clear"></div>
		<button class="btn btn-success" id="create_new_filter">Сохранить</button>
		<button class="btn btn-primary" id="update_filter">Обновить</button>
		<button class="btn btn-danger" id="clear_values">Очистить</button>
	</fieldset>
	<script>
		//Инициализация переменных--------------------------------------------------------------------------------------!
		var DATA = JSON.parse('<?php echo getDictionaryForFilters($conn) ?>'),
			filterParamObj = {
				group_id:	   -1,
				cons_agent:	   -1,
				cons_ki:	   -1,
				cons_mbki:	   -1,
				cons_color:	   -1,
				cons_group:    -1,
				cons_priority: -1,
				cons_sum_min:  -1,
				cons_sum_max:  -1,
				cons_ubki_min: -1,
				cons_ubki_max: -1
			},
			targSelArr = [
				'#cons_agent',
				'#cons_mbki',
				'#cons_ki',
				'#cons_color'];
		//--------------------------------------------------------------------------------------------------------------!

		if (DATA.error) {
			alert('Ошибка получения данных из БД обратитесь к администратору!!!');
		}
		else {
			var data = DATA.result;
			//console.dir(data);

			//Заполнение options данными с БД//----------------------------------------------------------------------------!
			data.DICT.curragentcode.forEach(function (el) {
				$(targSelArr[0]).append('<option id=' + el.CODE + '>' + el.CODE + ' - ' + el.AGENT + '</option>');
			});

			data.DICT.currriskgradembki.forEach(function (el) {
				$(targSelArr[1]).append('<option id=' + el.ID + '>' + el.RISKGRADE + '</option>');
			});

			data.DICT.currki.forEach(function (el) {
				$(targSelArr[2]).append('<option id=' + el.ID + '>' + el.DESCRIPTION + '</option>');
			});

			data.DICT.currhexcolor.forEach(function (el) {
				$(targSelArr[3]).append('<option id=' + el.HEX_COLOR_CODE + '>' + getDescForColor(el.DESCRIPTION) + '</option>');
			});
			//--------------------------------------------------------------------------------------------------------------!

			$('.paramsConstractor').change(function () {
				filterParamObj[this.id] = $(this).val() || -1;

				//Запонение поля приоритет при смене цвета
				if (this.id === 'cons_color') {
					$('#cons_priority').val($(this).children(':selected').text().split(' - ')[0]);
					filterParamObj[$('#cons_priority').attr('id')] = $('#cons_priority').val();
				}

			});

			//Апдейт блока где перечислены все мультиселекты
			$('.paramsConstractorSel').change(setDescription);

			for (var i = 0, max = data.GRUPS.length; i < max; i++) {

				$('#ready_filter').append(
						'<tr class="readyFilters" id="' + data.GRUPS[i].GROUP_ID + '">' +
							'<td id="grou_name">' + data.GRUPS[i].GROUP_NAME + '</td>' +
							'<td id="grup_color">' + getDescForColor(data.GRUPS[i].HEX_COLOR_DESCRIPTION) + '</td>' +
							'<td id="grou_credite_data">02.02.2016</td>' +
							'<td><div id="remove_filter" class="action_btn del_btn"></div></td>' +
						'</tr>'
				);

				$('#' + data.GRUPS[i].GROUP_ID).data({
					group_id: data.GRUPS[i].GROUP_ID || -1,
					cons_agent: data.GRUPS[i].AGENT_CODE_CURR || -1,
					cons_ki: data.GRUPS[i].CREDIT_HISTORY_CURR || -1,
					cons_mbki: data.GRUPS[i].MBKI_SCORE_CURR || -1,
					cons_color: data.GRUPS[i].HEX_COLOR_CODE || -1,
					cons_group: data.GRUPS[i].GROUP_NAME || -1,
					cons_priority: data.GRUPS[i].GROUP_RATING || -1,
					cons_sum_min: data.GRUPS[i].LOAN_AMOUNT_MIN || -1,
					cons_sum_max: data.GRUPS[i].LOAN_AMOUNT_MAX || -1,
					cons_ubki_min: data.GRUPS[i].UBKI_SCORE_MIN || -1,
					cons_ubki_max: data.GRUPS[i].UBKI_SCORE_MAX || -1
				});
				//console.dir($('#' + data.GRUPS[i].GROUP_ID).data());
			}

			$('.readyFilters').click(function (event) {
				if (event.target.id !== 'remove_filter') {
					//смена кнопок
					$('#update_filter').show();
					$('#create_new_filter').hide();

					//Очищаем обект
					filterParamObj = {};

					filterParamObj.group_id = this.id;

					$('.selected').empty(); //Очищаем поля с описанием выбраных полей
					$('.paramsConstractor').val('');
					var obj = $('#' + this.id).data(), i;

					console.dir(obj);
					for (i in obj) {
						var itemElem = $('#' + i);

						if (obj.hasOwnProperty(i)) {
							if (i === 'cons_agent' || i === 'cons_ki' || i === 'cons_mbki') {
								var curText = $(this).closest('div').find('.selected').text();
								itemElem.children().each(function () {
									//Очищаем старые записи
									if ($(this).prop('selected')) {
										$(this).prop({'selected': false});
									}
									//Делаем активными строки совпадающие со значениями из бд
									for (var y = 0, max = obj[i].length; y < max; y++) {
										//в cons_agent поле ID называется CODE
										if (i === 'cons_agent') {
											if ($(this).attr('id') === obj[i][y].CODE) {
												$(this).attr({'selected': 'selected'});
												$(this).closest('div').find('.selected').text(curText += $(this).text() + ", ");
											}
										}
										else {
											if ($(this).attr('id') === obj[i][y].ID) {
												$(this).attr({'selected': 'selected'});
												$(this).closest('div').find('.selected').text(curText += $(this).text() + ", ");
											}
										}
									}

								});
								//Обрезаем последнюю запятую в конечном варрианте
								itemElem.closest('div').find('.selected').text(curText.substring(0, curText.length - 2));
							}
							else if (i === 'cons_color') {
								filterParamObj[i] = obj[i];
								itemElem.children().each(function () {
									if ($(this).attr('id') == obj[i]) {
										$(this).attr({'selected': 'selected'});
									}
								});
							}
							else {
								filterParamObj[i] = obj[i];
								itemElem.val(obj[i]);
							}
						}
					}
				}
			});

			$('#create_new_filter').click(function () {
				getAllSelectedItems('.paramsConstractorSel');
				if(filterParamObj.cons_group !== -1 && filterParamObj.cons_color !== -1) {
					if(thereIsANameOrPriority()) {
						if(countParam() >= 4) {
							if(confirm('Создать новый фильтр с названием: "' + filterParamObj.cons_group + '" ?')) {
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
								console.dir(filterParamObj);
							}
						}
						else {
							alert('Необходимо заполнить хотябы одно значение помимо обязательных!');
						}
					}
				}
				else {
					alert('Не заполнены одязательные поля! (Группа, Цвет, Приоритет)');
				}
			});

			$('#update_filter').click(function () {
				getAllSelectedItems('.paramsConstractorSel');
				if(filterParamObj.cons_group !== -1 && filterParamObj.cons_color !== -1) {
					if(thereIsANameOrPriority()) {
						if(countParam() >= 5) {
							if(confirm('Изменить фильтр: "' + filterParamObj.cons_group + '" ?')) {
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
								console.dir(filterParamObj);
							}
						}
						else {
							alert('Необходимо заполнить хотябы одно значение помимо обязательных!');
						}
					}
				}
				else {
					alert('Не заполнены одязательные поля! (Группа, Цвет, Приоритет)');
				}
			});

			$('.readyFilters #remove_filter').click(function () {
				if(confirm('Удалить фильтр: "' + $(this).closest('.readyFilters').find('#grou_name').text() + '" ?')) {
					var remFilterId = $(this).closest('.readyFilters').attr('id');
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				}
				//Дописать: хачу чтоб чистил поля если удаляемый фильтр открыт для редактирования
			});

			$('#clear_values').click(function () {
				//Смена кнопок
				$('#update_filter').hide();
				$('#create_new_filter').show();
				$('.selected').empty();
				//Убираем все выбраные поля + значения
				$('.paramsConstractor, .paramsConstractorSel').each(function () {
					if (this.id === 'cons_color' || this.id === 'cons_agent' || this.id === 'cons_ki' || this.id === 'cons_mbki') {
						$(this).children().each(function () {
							$(this).prop('selected', false);
						});
					}
					else {
						$(this).each(function () {
							$(this).val('');
						});
					}
				});
				//Затираем все данные в обекте до дефолтных '-1'
				for (var i in filterParamObj) {
					filterParamObj[i] = -1;
				}
			});
		}

		/**
		 * считает кол выбраных параметров в объекте
		 *
		 * @returns {int} кол выбраных параметров в объекте
		 **/
		function countParam() {
			var cPar = 0;
			for (var i in filterParamObj) if(filterParamObj[i] !== -1) cPar++;
			return cPar;
		};

		/**
		 * Проверкяет существуют ли групы с таким названием или приоритетом(цветом)
		 * (Для груп которые редактируются - игнорирует свое название и приоритет в проверке)
		 *
		 * @returns {boolean} результат проверки
		 **/
		function thereIsANameOrPriority() {
			var nameCheckAndPriority = true;
			var curFillId = filterParamObj.group_id;
			$('#ready_filter').children().each(function(i, item){
				//если id обекта и существующей группы разные выполняем проверку
				if (curFillId != $(item).attr('id')) {
					var priority = $(item).find('#grup_color').text().substring(0, 1),
							name = $(item).find('#grou_name').text();

					if(priority == filterParamObj.cons_priority) {
						nameCheckAndPriority = false;
						alert('Група с приоритетом: "' + priority + '" уже существует!');
						return false;
					}
					else if(name.toLowerCase() == filterParamObj.cons_group.toLowerCase()) {
						nameCheckAndPriority = false;
						alert('Група с названием: "' + name + '" уже существует!');
						return false;
					}
				}
			});
			return nameCheckAndPriority;
		};

		/**
		 * Проверка на валидность пар полей от и до, от должно быть меньше чем до
		 *
		 * @returns {boolean} результат проверки
         */
		function validMinAndMaxVal() {
			var S  = filterParamObj.cons_sum_min,
				SM = filterParamObj.cons_sum_max,
				R  = filterParamObj.cons_ubki_min,
				RM = filterParamObj.cons_ubki_max;
			return (((!S || !SM || S <= SM) ? true : false) && ((!R || !RM || R <= RM) ? true : false)) ? true : false;
		};

		/**
		 * Собирает все выбраные options и записивает их в свойство обекта через разделитель '||'
		 * если выбраных значений свойства больше одного
		 *
		 * @param (selClass) class елемента с которого нужно собрать выбраные option (указать с точкой '.' + class)
         */
		function getAllSelectedItems(selClass) {
			//Очищаем поля с селектами чтоб собрать новые наборы через "||"
			filterParamObj.cons_agent = '';
			filterParamObj.cons_mbki = '';
			filterParamObj.cons_ki = '';

			//Заполняем значениями если запись в свойстве первая не ставим разделитель
			$(selClass).children().each(function(i, item) {
				var ID = $(this).parent().attr('id');
				if(item.selected) filterParamObj[ID] += filterParamObj[ID].length ? ('||' + item.id) : item.id;
			});

			//если поля пустые присваеваем им значение -1
			filterParamObj.cons_agent = filterParamObj.cons_agent || -1;
			filterParamObj.cons_mbki = filterParamObj.cons_mbki || -1;
			filterParamObj.cons_ki = filterParamObj.cons_ki || -1;
		}

		/**
		 * Возвращает значение цвета на рус языке + приоритет цвета
		 *
		 * @param (color) цвет из базы на англ языке
		 * @returns {string} цвет
         */
		function getDescForColor(color) {
			var colorDesc = {
				red: 			'1 - Красный',
				green: 			'2 - Зеленый',
				blue: 			'3 - Синий',
				gray: 			'4 - Серый',
				orange: 		'5 - Оранжевый',
				yellow: 		'6 - Желтый',
				purple: 		'7 - Сереневый',
				'light blue': 	'8 - Голубой',
				pink: 			'9 - Розовый',
				'light green': 	'10 - Салатовый'
			};
			return colorDesc[color];
		}

		/**
		 * Дублирует выбраные записи под блоками селект
		 */
		function setDescription() {
			var str = "";
			$(this).children(':selected').each(function() {
				str += $(this).text() + ", ";
			});
			$(this).parent().find('.selected').text(str.substr(0, str.length -2));
		};
	</script>
<?php
}
?>