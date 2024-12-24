describe('Login Page Tests', () => {
  const baseUrl = 'https://jti-sphere.ngrok.app';

  beforeEach(() => {
      cy.visit(`${baseUrl}/login`);
  });

  it('harus tampil laman login', () => {
      cy.get('input[name="username"]').should('be.visible');
      cy.get('input[name="password"]').should('be.visible');
      cy.get('button[type="submit"]').should('be.visible');
  });

  it('harus error saat kosong', () => {
      cy.get('button[type="submit"]').click();

      cy.contains('Field is required').should('be.visible');
  });

  it('harus error karena salah', () => {
      cy.get('input[name="username"]').type('salah');
      cy.get('input[name="password"]').type('salah');
      cy.get('button[type="submit"]').click();

      cy.contains('Login gagal').should('be.visible');
  });

  it('harus sukses karena benar', () => {
      cy.get('input[name="username"]').type('administrator');
      cy.get('input[name="password"]').type('12345');
      cy.get('button[type="submit"]').click();

      cy.url().should('include', '/home');
      cy.contains('Welcome').should('be.visible');
  });
});
