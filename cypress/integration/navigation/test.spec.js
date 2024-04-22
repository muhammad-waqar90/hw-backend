describe('Open website', () => {
  it('Visits the app root url', () => {
    cy.visit('/');
    cy.contains('h2', 'Welcome to Hijaz World!');
  });
});