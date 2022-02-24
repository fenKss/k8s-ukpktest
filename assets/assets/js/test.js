const $test = $(`#test`);
$test.tabs();

class Test {

  constructor (testId, token) {
    this.testId = testId;
    this.init.elements();
    this.init.listeners();
    this.init.vars();
    this.init.timer();
    this.token = token;

    const date = new Date(parseInt(this.$savedResults.text() * 1000));
    this.updateSavedAt(date);

    const $questions = this.$questions.find(
      `li[data-question-id]`);
    $questions.each( (i,li) => {
      const $li =$(li),
            questionId = $li.attr('data-question-id'),
            $tab = this.$tabs.find(`[data-question-id="${questionId}"]`);
      if ($tab.find(`input:checked`).length) {
        $li.addClass('answered');
      } else {
        $li.removeClass('answered');
      }
    })

    setInterval(async () => {
      if(JSON.stringify(this.lastSavedResults) !== JSON.stringify(this.answers)){
        await this.api.answer();
      }
    }, 20000)
  }

  init = {
    elements : () => {
      this.$root = $(`#test`);
      if (!this.$root.length) {
        throw new Error('Root not found');
      }
      this.$timer = $('.time .expired span');
      if (!this.$timer.length) {
        throw new Error('Timer not found');
      }
      this.$savedResults = $('.time .saved span');
      if (!this.$savedResults.length) {
        throw new Error('SavedResults not found');
      }
      this.$saveButton = $('button.save');
      if (!this.$saveButton.length) {
        throw new Error('SaveButton not found');
      }
      this.$questions = this.$root.find(`ul.questions`);
      if (!this.$questions.length) {
        throw new Error('Questions ul not found');
      }
      this.$tabs = this.$root.find(`div.tab-content`);
      if (!this.$tabs.length) {
        throw new Error('Tabs not found');
      }
    },
    vars     : () => {
      this.url = this.generateBaseUrl();
      this.apiUrl = this.url + `/api/test/${this.testId}/`;
      this.answers = {};
      this.answers = this.answer(this.$tabs.find('.tab form'));
      this.lastSavedResults = this.answers;
    },
    listeners: () => {
      const self = this;
      $(document).on('change', 'form[name="answer"]', (e) => {
        const $this   = $(e.currentTarget),
              $target = $(e.target);
        const answers =  self.answer($this);
        for (const key in answers){
          this.answers[key] = answers[key];
        }

        const $tab = $this
                         .closest(`[data-question-id]`);
        const questionId = $tab.attr(`data-question-id`);
        const $li = this.$questions.find(
          `li[data-question-id=${questionId}]`);

        if ($tab.find(`input:checked`).length) {
          $li.addClass('answered');
        } else {
          $li.removeClass('answered');
        }
      });
      this.$saveButton.click(async () => {
        const answer = confirm(`Вы уверены, что хотите завершить тест?`);
        if (answer){
          await this.api.answer();
          await this.api.saveResult();
          location.reload();
        }
      })

    },
    timer    : () => {
      const countdown = $('.time .expired span');
      const target_date = new Date(countdown.text());

      const interval = setInterval(function () { getCountdown(); }, 1000);

      getCountdown();

      function getCountdown () {
        if ((target_date - new Date()) < 0) {
          //TODO Заменить на
          // location.reload();
          console.log('Время истекло');
          clearInterval(interval);
          return;
        }
        const current_date = new Date().getTime();
        let seconds_left = (target_date - current_date) / 1000;

        const days = pad(parseInt(seconds_left / 86400));
        seconds_left = seconds_left % 86400;

        const hours = pad(parseInt(seconds_left / 3600));
        seconds_left = seconds_left % 3600;

        const minutes = pad(parseInt(seconds_left / 60));
        const seconds = pad(parseInt(seconds_left % 60));

        countdown.html(
          days + ' : ' + hours + ' : ' + minutes + ' : ' + seconds);
      }

      function pad (n) {
        return (n < 10 ? '0' : '') + n;
      }
    },
  };
  updateSavedAt = (date) => {
    function pad (n) {
      return (n < 10 ? '0' : '') + n;
    }

    const hours   = pad(date.getHours()),
          minutes = pad(date.getMinutes()),
          seconds = pad(date.getSeconds()),
          year    = pad(date.getFullYear()),
          month   = pad(date.getMonth() + 1),
          day     = pad(date.getDate());
    this.$savedResults.html(`${year}-${month}-${day} ${hours}:${minutes}:${seconds}`);
  };
  api = {
    request: async (url, data = [], method = 'GET') => {
      const settings = {
        method,
      };
      if (method.toLowerCase() === 'post') {
        function getFormData (object) {
          const formData = new FormData(this);
          for (const key in object) {
            if (object.hasOwnProperty(key)) {
              formData.append(key, object[key]);
            }
          }
          return formData;
        }

        settings['body'] = getFormData(data);

      } else if (method.toLowerCase() === 'get') {
        url = new URL(url);
        for (let key in data) {
          url.searchParams.append(key, data[key]);
        }
        settings.headers = {
          'Content-Type': 'application/json;charset=utf-8',
        };
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
    answer : async () => {
      let url = this.apiUrl + `answer`;
      await this.api.request(url,
        { 'answers': JSON.stringify(this.answers), '_token': this.token },
        'post')
          .then(data => {
            this.updateSavedAt(new Date())
            this.lastSavedResults = {...this.answers};
            return true;
          })
          .catch(e => {throw e;});
    },
    saveResult: () => {
      let url = this.apiUrl + `answer/all`;
      return this.api.request(url, {'_token': this.token}, 'post');
    },
  };
  answer = ($form) => {
    const answers = {};
    $form.each((i, form) => {
      const $form      = $(form),
            questionId = $form.find(`input[name="question_id"]`).val(),
            inputs     = $form.find('input:checked');
      answers[questionId] = [];
      inputs.each((i, input) => {
        const $input = $(input);
        const value = parseInt($input.val());
        answers[questionId].push(value);
      });
    });
    return answers;
  };
  generateBaseUrl = () => {
    let baseUrl = window.location.protocol + '//' + window.location.hostname;
    if (window.location.port) baseUrl += ':' + window.location.port;
    return baseUrl;
  };
}
