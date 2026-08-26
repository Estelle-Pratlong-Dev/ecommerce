import { Controller } from '@hotwired/stimulus';

/*
 * Replie / déplie le panneau de filtres de la boutique.
 * S'applique à .catalog-layout (data-controller="filters").
 * Le bouton porte data-action="filters#toggle".
 * L'état est mémorisé dans le navigateur (localStorage).
 */
export default class extends Controller {
    static targets = ['icon'];

    connect() {
        if (localStorage.getItem('shopFiltersCollapsed') === '1') {
            this.element.classList.add('is-collapsed');
            this.#syncIcon();
        }
    }

    toggle() {
        this.element.classList.toggle('is-collapsed');
        localStorage.setItem(
            'shopFiltersCollapsed',
            this.element.classList.contains('is-collapsed') ? '1' : '0',
        );
        this.#syncIcon();
    }

    #syncIcon() {
        if (this.hasIconTarget) {
            this.iconTarget.textContent = this.element.classList.contains('is-collapsed') ? '▸' : '▾';
        }
    }
}
