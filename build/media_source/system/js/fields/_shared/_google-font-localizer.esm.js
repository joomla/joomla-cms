// https://www.webpagetest.org/result/201003_Di3P_561f21bbe231da8eea0547d2be0de7c9/2/details/#waterfall_view_step1
import redaxios from 'redaxios';

export class GoogleFontLocaliser extends HTMLElement {
  constructor() {
    super();

    this.fetch = this.fetch.bind(this);
  }

  connectedCallback() {
    this.innerHTML = `
<label>Enter a Google Font name: <input type="text" value="" /></label>
<button type="button" class="btn btn-primary">Localize it!</button>
`;

    this.input = this.querySelector('input');
    this.button = this.querySelector('button');
    this.button.addEventListener('click', this.fetch);
  }

  disconnectedCallback() {
    this.button.removeEventListener('click', this.fetch);
  }

  async fetch() {
    const dataO = {};

    redaxios({
      method: 'get',
      url: `https://fonts.googleapis.com/css?family=${this.input.value}:400,400i,800,800i&display=swap`,
    })
      .then(async (res) => {
        const body = res.data;
        const matchFontFilesRegex = /url\((https\:\/\/fonts\.gstatic\.com\/.*)\) format/gm;

        dataO.original_stylesheet = body;
        dataO.local_stylesheet = body;
        dataO.font_urls = [...body.matchAll(matchFontFilesRegex)];
        dataO.local_font_paths = dataO.font_urls.map(c => `fonts/${c[1].split('/')
          .slice(4)
          .join('/')}`);
        dataO.fonts = dataO.font_urls.map((c, index) => ({
          remote: c[1],
          local: dataO.local_font_paths[index],
          file: null,
        }));
        dataO.processed = [];

        console.log(`Detected ${dataO.fonts.length} font files to download.`);
        console.log(dataO)

        dataO.fonts.map((c) => {
          dataO.local_stylesheet = dataO.local_stylesheet.replace(c.remote, `../../../${c.local}`);

          if (dataO.processed.includes(c.remote)) {
           // Already fetched
           return;
          }

          dataO.processed.push(c.remote);

          redaxios({
            method: 'get',
            url: c.remote,
            encoding: null
          })
            .then(re => {
              // Store font locally
              c.file = re.data;

              redaxios({
                method: 'post',
                url: `index.php?option=com_templates&task=template.saveAsset&${this.getAttribute('token')}=1`,
                data: {
                  fileData: re.data,
                  fileName: c.local,
                  fileType: 'woff2',
                  template: this.getAttribute('template'),
                  client: this.getAttribute('client'),
                  epoch: this.getAttribute('timestamp'),
                  fieldId: this.getAttribute('id'),
                }
              });
            })
        });

        console.log(`Downladed: ${dataO.processed.length} fonts`);

        // Store the css
        redaxios({
          method: 'post',
          url: `index.php?option=com_templates&task=template.saveAsset&${this.getAttribute('token')}=1`,
          data: {
            fileData: dataO.local_stylesheet,
            fileName: `css/vendor/GoogleFonts/${this.input.value.replace(' ', '').toLowerCase()}.css`,
            fileType: 'css',
            template: this.getAttribute('template'),
            client: this.getAttribute('client'),
            epoch: this.getAttribute('timestamp'),
            fieldId: this.getAttribute('id'),
          }
        });
      });
  }
}

