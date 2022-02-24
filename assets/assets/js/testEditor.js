class TestEditor {
  btn = {
    $addQuestion: null,
  };

  constructor () {
    this.init.elements();
    this.init.listeners();
    this.init.vars();
  }

  init = {
    elements : () => {
      this.$root = $(`#test-editor`);
      if (!this.$root.length) {
        throw new Error('Root not found');
      }

      this.form.$form = $(`#testEditorForm`);
      if (!this.form.$form.length) {
        throw new Error('Form not found');
      }
      this.form.$form.on('submit', () => false);
      this.form.$input = this.form.$form.find(`input[name='name']`);
      if (!this.form.$input.length) {
        throw new Error('Form input not found');
      }
      this.$questions = this.$root.find('ul.questions');
      if (!this.$questions.length) {
        throw new Error('Questions ul not found');
      }
      this.$tabs = this.$root.find(`div.tab-content`);
      if (!this.$tabs.length) {
        throw new Error('Tabs not found');
      }
      this.btn.$addQuestion = $(`#add-question-btn`);
      if (!this.btn.$addQuestion.length) {
        throw new Error('Add question button not found');
      }
    },
    vars     : () => {
      this.url = this.generateBaseUrl();
      this.variantId = this.form.$form.find(`input[name="variant"]`).val();
      this.apiUrl = this.url + `/api/variant/${this.variantId}/`;
    },
    listeners: () => {
      const self = this;
      this.btn.$addQuestion.click(async () => {
        this.api.question.add();
      });

      this.form.$form.find(`.close`).click(() => {
        this.form.hide();
      });
      const getQuestionId = ($this) => {
        return $this.closest(`[data-question-id]`)
                    .first()
                    .attr(`data-question-id`);
      }
      $(document).on('click', 'button.edit-question-title', function (e) {
        e.preventDefault();
        const $this = $(this),
              questionId = getQuestionId($this),
              title      = $this.parent().find(`span`).text();
        self.form.$input.val(title);
        self.form.show($this);
        self.form.$form.on('submit', () => {
          self.api.question.editTitle(questionId);
        });
      });

      $(document).on('click', 'button.edit-option-title', function (e) {
        e.preventDefault();
        const $this = $(this),
              questionId = getQuestionId($this),
              $label = $this.parent().find(`label`);
        self.form.$input.val($label.text().trim());
        self.form.show($this);
        self.form.$form.on('submit', () => {
          self.api.option.editTitle(questionId,$label ,$this );
        });
      });

      $(document).on('click', 'button.add-option', function () {
        const $this = $(this),
              questionId = getQuestionId($this);
        self.api.option.add(questionId);
      });

      $(document).on('change', 'input.edit-question-type', function () {
        const $this = $(this),
              questionId = getQuestionId($this);
        self.api.question.editType($this.is(':checked') ? 1 : 0, questionId);
      });

      $(document).on('change', 'form[name="question_option_edit_correct"]', function (e){
        const $this = $(this),
              $target = $(e.target),
              questionId = getQuestionId($this);
        self.api.option.correct($target,questionId);
      })

    },
  };
  question = {
    addToDocument: (questionId, data) => {
      const $data            = $(data),
            questionSelector = `ul.questions li[data-question-id=${questionId}]`,
            tabSelector      = `.tab[data-question-id=${questionId}]`,
            $question        = $data.find(questionSelector),
            $tab             = $data.find(tabSelector);
      this.$questions.find(`li`).removeClass(`active`);
      this.$tabs.find(`.tab`).removeClass(`active`);
      const $existQuestion = this.$root.find(questionSelector),
            $existTab      = this.$root.find(tabSelector);
      if ($existQuestion.length && $existTab.length) {
        $existQuestion.replaceWith($question);
        $existTab.replaceWith($tab);
      } else {
        $question.appendTo(this.$questions);
        $tab.appendTo(this.$tabs);
      }
    },
  };
  api = {
    request : async (url, data = [], method = 'GET') => {
      const settings = {
        method,
      };
      if (method.toLowerCase() === 'post') {
        function getFormData(object) {
          const formData = new FormData(this);
          for (const key in object) {
            formData.append(key, object[key]);
          }
          return formData;
        }

        settings['body'] = getFormData(data);

      } else if (method.toLowerCase() === 'get') {
        url = new URL(url);
        for (let key in data) {
          url.searchParams.append(key, data[key]);
        }
        settings.headers= {
          'Content-Type': 'application/json;charset=utf-8',
        }
      }

      const response = await fetch(url, settings);

      if (response.ok) {
        const text = await response.text();
        let json;
        try {
          json = JSON.parse(text);
        } catch (e) {
          return text;
        }
        if (json.error) {
          throw json.error_msg;
        } else {
          return json.data;
        }

      } else {
        throw new Error(
          `Request error ` + (response.statusText || response.status));
      }
    },
    question: {
      add      : () => {
        let url = this.apiUrl + `question/add`;
        let questionId;
        this.api.request(url, [], 'post')
            .then(data => {
              return data.id;
            })
            .catch(e => {throw e;})
            .then(async id => {
              questionId = id;
              return await this.api.question.get(questionId);
            })
            .then(data => {
              this.question.addToDocument(questionId, data);

            })
            .catch(e => {throw e;});

      },
      editTitle: (questionId) => {
        const title = this.form.$input.val();
        let url = this.apiUrl + `question/${questionId}/edit/title`;
        const $elems = $(`li[data-question-id="${questionId}"] > a, div.tab[data-question-id="${questionId}"] .question-title span`);
        if ($elems.first().text() !== title) {
          this.api.request(url, { title })
              .then(data => {
                $elems.text(title);
              })
              .catch(e => {throw e;});
        }
        this.form.hide();
      },
      get      : async (questionId) => {
        const url = this.url +
          `/admin/variant/${this.variantId}/question/${questionId}`;
        return await this.api.request(url);
      },
      editType: (type,questionId) => {
        let url = this.apiUrl + `question/${questionId}/edit/type`;
        this.api.request(url, {type}, 'post')
            .then(data => {
              return data.id;
            })
            .catch(e => {throw e;})
            .then(async id => {
              return await this.api.question.get(questionId);
            })
            .then((data) => {
              this.question.addToDocument(questionId, data);
            })
            .catch(e => {throw e;});
      }
    },
    option  : {
      add: (questionId) => {
        let url = this.apiUrl + `question/${questionId}/option/add`;
        this.api.request(url, [], 'post')
            .then(data => {
              return data.id;
            })
            .catch(e => {throw e;})
            .then(async id => {
              return await this.api.question.get(questionId);
            })
            .then((data) => {
              this.question.addToDocument(questionId, data);
            })
            .catch(e => {throw e;});

      },
      correct:($target,questionId) => {
        let url = this.url + $target.attr('data-url');
        this.api.request(url, [], 'post')
            .then(data => {
              return data.id;
            })
            .catch(e => {throw e;})
            .then(async id => {
              return await this.api.question.get(questionId);
            })
            .then((data) => {
              this.question.addToDocument(questionId, data);
            })
            .catch(e => {throw e;});
      },
      editTitle: (questionId, $label, $button) => {
        let url = this.url + $button.attr('data-url');
        const title = this.form.$input.val().trim();
        if ($label?.text()?.trim() !== title)
          this.api.request(url, { title })
              .then(data => {
                $label.find('span').text(title);
              })
              .catch(e => {throw e;});
        this.form.hide();
      },
    },
  };
  form = {
    show: ($object) => {
      const pos = $object.offset();
      pos.top = pos.top + 10;
      pos.left = pos.left + 10;
      this.form.$form.removeClass('hidden').offset(pos);
    },
    hide: () => {
      this.form.$form.addClass('hidden');
      this.form.$form.off('submit');
      this.form.$form.on('submit', () => false);
    },
  };

  generateBaseUrl = () => {
    let baseUrl = window.location.protocol + '//' + window.location.hostname;
    if (window.location.port) baseUrl += ':' + window.location.port;
    return baseUrl;
  };
}