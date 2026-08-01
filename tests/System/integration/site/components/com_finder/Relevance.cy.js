// Invented six letter tokens. Neither can appear in the stopword list, which
// would divide the token weight by 8, and neither is altered by the stemmer.
// Both are the same length, so both carry the same token weight and the
// comparison reduces cleanly to "term frequency x inverse document frequency".
const termCommon = 'zorbex';
const termRare = 'quilon';

const aliasPrefix = 'finderidf-';
const decoyTitle = 'Finder IDF Corpus Decoy';
const targetTitle = 'Finder IDF Corpus Target';

// How many filler articles carry the common term, and how many carry neither.
const fillerWithCommon = 12;
const fillerNeutral = 3;

/**
 * Occurrence counts for the two articles under test.
 *
 * The decoy holds more matching words in total (6 against 5), so a ranking that
 * only counts term frequency puts it first. The target concentrates its matches
 * in the rare term, so weighting each match by how rare its term is has to lift
 * the target above the decoy.
 *
 * Writing out the comparison, the target wins exactly when
 * (targetRare - decoyRare) * idfRare > (decoyCommon - targetCommon) * idfCommon,
 * so with these counts the rare term needs an idf at least 4/3 of the common
 * one. The corpus gives it roughly 1.7 times, which holds comfortably across a
 * wide range of site sizes.
 *
 * The margin is deliberately modest rather than dramatic: idf alone cannot
 * overcome a large term frequency advantage, because the scoring model has no
 * term frequency saturation. See search.md section 4.3.
 */
const decoyCommon = 5;
const decoyRare = 1;
const targetCommon = 1;
const targetRare = 4;

/**
 * Ordinary English sentences used as background text. Real prose is deliberate:
 * it gives the index a naturally skewed vocabulary, which is the distribution an
 * inverse document frequency factor exists to exploit.
 */
const filler = [
  'The harbour master kept a careful record of every vessel that arrived before dawn.',
  'Autumn rain collected in the gutters and ran down towards the old stone bridge.',
  'She read the letter twice, then folded it away without saying anything at all.',
  'A long train of carts moved slowly along the road that led out of the valley.',
  'The library closed early on Thursdays, which nobody in the town ever remembered.',
  'Bread, cheese and a little salt were all that remained in the kitchen cupboard.',
  'He had walked these fields since childhood and knew every hedge and ditch by name.',
  'The clock in the hallway had been wrong by seven minutes for as long as anyone knew.',
];

/**
 * Build the intro text for one article. Both tokens go into the body only, never
 * the title, so both sides of the comparison share one context multiplier and it
 * cancels out of the arithmetic.
 */
const buildBody = (index, commonCount, rareCount) => {
  const parts = [filler[index % filler.length]];

  for (let i = 0; i < commonCount; i += 1) {
    parts.push(`The survey mentions ${termCommon} in passing.`);
  }

  for (let i = 0; i < rareCount; i += 1) {
    parts.push(`The appendix discusses ${termRare} at some length.`);
  }

  parts.push(filler[(index + 3) % filler.length]);

  return `<p>${parts.join(' ')}</p>`;
};

/**
 * Describe every article in the corpus.
 */
const buildCorpus = () => {
  const articles = [];

  for (let i = 1; i <= fillerWithCommon; i += 1) {
    articles.push({
      title: `Finder IDF Corpus Filler ${i}`,
      alias: `${aliasPrefix}filler-${i}`,
      common: 1,
      rare: 0,
    });
  }

  for (let i = 1; i <= fillerNeutral; i += 1) {
    articles.push({
      title: `Finder IDF Corpus Neutral ${i}`,
      alias: `${aliasPrefix}neutral-${i}`,
      common: 0,
      rare: 0,
    });
  }

  articles.push({
    title: decoyTitle, alias: `${aliasPrefix}decoy`, common: decoyCommon, rare: decoyRare,
  });

  articles.push({
    title: targetTitle, alias: `${aliasPrefix}target`, common: targetCommon, rare: targetRare,
  });

  return articles.map((article, index) => ({ ...article, index }));
};

/**
 * Return the result titles of the current search page, in rendered order.
 */
const resultTitles = () => cy.get('.result__title-text')
  .then(($titles) => Cypress._.map($titles, (element) => element.textContent.trim()));

/**
 * Open a search and wait until the corpus is actually searchable.
 *
 * The articles are indexed during the API save, but after a burst of saves the
 * results are not always visible to a search straight away. Reload until they
 * are, so that the ordering assertions are not racing the index.
 */
const searchFor = (terms) => {
  const visit = (attempt) => {
    cy.visit(`/index.php?option=com_finder&view=search&q=${terms}`);

    cy.get('body').then(($body) => {
      if ($body.find('.result__title-text').length === 0 && attempt < 30) {
        // eslint-disable-next-line cypress/no-unnecessary-waiting
        cy.wait(2000);
        visit(attempt + 1);
      }
    });
  };

  visit(0);
};

describe('Test that smart search ranks results by relevance', () => {
  // Ids of the articles created for this run, so they can be removed again.
  const createdIds = [];

  before(() => {
    /**
     * No explicit reindex is needed. Saving an article fires onContentAfterSave,
     * which the Smart Search content plugin turns into onFinderAfterSave, and the
     * content adapter indexes the item there and then. That happens on the API
     * save path just as it does in the administrator.
     */
    cy.db_createCategory({ extension: 'com_content' }).then((categoryId) => {
      buildCorpus().forEach((article) => {
        cy.api_post('/content/articles', {
          title: article.title,
          alias: article.alias,
          catid: categoryId,
          introtext: buildBody(article.index, article.common, article.rare),
          fulltext: '',
          state: 1,
          access: 1,
          language: '*',
          created: '2023-01-01 20:00:00',
          modified: '2023-01-01 20:00:00',
          images: '',
          urls: '',
          attribs: '',
          metadesc: '',
          metadata: '',
        }).then((response) => {
          createdIds.push(response.body.data.id);
        });
      });
    });
  });

  after(() => {
    createdIds.forEach((id) => {
      // An article has to be trashed before the API will delete it.
      cy.api_patch(`/content/articles/${id}`, { state: -2 });
      cy.api_delete(`/content/articles/${id}`);
    });
  });

  it('can rank a rare term above a frequent one', () => {
    searchFor(`${termCommon}+${termRare}`);

    resultTitles().then((titles) => {
      const targetPosition = titles.indexOf(targetTitle);
      const decoyPosition = titles.indexOf(decoyTitle);

      expect(targetPosition, 'target article is among the results').to.be.greaterThan(-1);
      expect(decoyPosition, 'decoy article is among the results').to.be.greaterThan(-1);
      expect(targetPosition, 'target outranks decoy').to.be.lessThan(decoyPosition);
    });
  });

  it('can keep frequency ordering for a single term query', () => {
    // A single term query applies one uniform factor to every match, so the
    // weighting must leave plain frequency ordering untouched. The decoy holds
    // the term five times, every filler article holds it once.
    searchFor(termCommon);

    resultTitles().then((titles) => {
      expect(titles[0], 'the most frequent match is first').to.equal(decoyTitle);
    });
  });
});
