import { Component, NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { ProfileComponent } from './profile.component';

@Component({
    standalone: false,
    template: `
        <mp-profile>
            <span title></span>
            <span action></span>
            <span class="default-slot"></span>
        </mp-profile>
    `,
})
class TestHostComponent {}

describe('ProfileComponent', () => {
    let fixture: ComponentFixture<TestHostComponent>;

    beforeEach(() => {
        TestBed.configureTestingModule({
            declarations: [ProfileComponent, TestHostComponent],
            schemas: [NO_ERRORS_SCHEMA],
        });

        fixture = TestBed.createComponent(TestHostComponent);
    });

    describe('Profile header', () => {
        it('should render <spy-headline> component', () => {
            fixture.detectChanges();
            const headlineComponent = fixture.debugElement.query(By.css('spy-headline'));

            expect(headlineComponent).toBeTruthy();
        });

        it('should render `title` slot to the <spy-headline> component', () => {
            fixture.detectChanges();
            const titleSlot = fixture.debugElement.query(By.css('spy-headline [title]'));

            expect(titleSlot).toBeTruthy();
        });

        it('should render `action` slot to the <spy-headline> component', () => {
            fixture.detectChanges();
            const actionSlot = fixture.debugElement.query(By.css('spy-headline [action]'));

            expect(actionSlot).toBeTruthy();
        });
    });

    describe('Profile content', () => {
        it('should render `.mp-profile__col--content` element', () => {
            fixture.detectChanges();
            const contentElem = fixture.debugElement.query(By.css('.mp-profile__col--content'));

            expect(contentElem).toBeTruthy();
        });

        it('should render default slot to the `.mp-profile__col--content` element', () => {
            fixture.detectChanges();
            const defaultSlot = fixture.debugElement.query(By.css('.mp-profile__col--content .default-slot'));

            expect(defaultSlot).toBeTruthy();
        });
    });
});
