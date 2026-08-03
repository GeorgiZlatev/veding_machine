async function request(action = 'state', data = {}) {
    const response = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, data })
    });

    const result = await response.json();
    if (!result.ok) {
        alert(result.message);
        return;
    }

    render(result.state, result.service.authorized);
    return result;
}

function render(state, serviceAuthorized) {
    document.querySelector('#balance').textContent = state.balance;

    const drinks = document.querySelector('#drinks');
    drinks.replaceChildren();

    Object.entries(state.drinks).forEach(([id, drink]) => {
        const line = document.createElement('p');
        const button = document.createElement('button');

        button.textContent = `Купи ${drink.name} (${drink.priceLabel})`;
        button.disabled = drink.quantity < 1;
        button.onclick = () => request('drink.buy', { id });

        line.append(button, `Наличност: ${drink.quantity}`);
        drinks.append(line);
    });

    const coins = document.querySelector('#coin-buttons');
    coins.replaceChildren();

    state.coins.forEach(coin => {
        const button = document.createElement('button');
        button.textContent = `Добави ${formatMinorUnits(coin, state.currency)}`;
        button.onclick = () => request('coin.insert', {
            value: coin / (10 ** state.currency.decimals),
        });

        coins.append(button);
    });

    const display = document.querySelector('#display');
    display.replaceChildren();

    state.messages.forEach(message => {
        const item = document.createElement('li');
        item.textContent = message;
        display.append(item);
    });

    renderServiceArea(serviceAuthorized);
}

function formatMinorUnits(value, currency) {
    const amount = (value / (10 ** currency.decimals)).toFixed(currency.decimals);
    return `${amount} ${currency.symbol}`;
}

function renderServiceArea(authorized) {
    const area = document.querySelector('#service-area');
    area.replaceChildren();

    if (!authorized) {
        const button = document.createElement('button');
        button.textContent = 'Вход в сервизни настройки';
        button.onclick = () => renderLoginForm(area);
        area.append(button);
        return;
    }

    const title = document.createElement('h2');
    title.textContent = 'Сервизни настройки';
    area.append(title, createDrinkForm(), createCoinForm());

    const logout = document.createElement('button');
    logout.textContent = 'Изход от сервизни настройки';
    logout.onclick = () => request('service.logout');
    area.append(logout);
}

function renderLoginForm(area) {
    area.replaceChildren();
    const form = document.createElement('form');
    const password = document.createElement('input');
    password.type = 'password';
    password.required = true;
    password.placeholder = 'Парола';

    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.textContent = 'Вход';
    form.append(password, submit);
    form.onsubmit = event => {
        event.preventDefault();
        request('service.login', { password: password.value });
    };
    area.append(form);
}

function createDrinkForm() {
    const form = document.createElement('form');
    const name = formField('Име', 'text');
    const price = formField('Цена', 'number', '0.01');
    const quantity = formField('Количество', 'number', '1');
    quantity.input.min = '0';
    quantity.input.step = '1';

    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.textContent = 'Добави напитка';
    form.append(name.label, price.label, quantity.label, submit);
    form.onsubmit = event => {
        event.preventDefault();
        request('service.drink.add', {
            name: name.input.value,
            price: price.input.value,
            quantity: quantity.input.value,
        });
    };
    return form;
}

function createCoinForm() {
    const form = document.createElement('form');
    const value = formField('Номинал', 'number', '0.01');
    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.textContent = 'Добави монета';
    form.append(value.label, submit);
    form.onsubmit = event => {
        event.preventDefault();
        request('service.coin.add', { value: value.input.value });
    };
    return form;
}

function formField(text, type, step = null) {
    const label = document.createElement('label');
    label.textContent = `${text}: `;
    const input = document.createElement('input');
    input.type = type;
    input.required = true;
    if (step) {
        input.step = step;
        input.min = step;
    }
    label.append(input);
    return { label, input };
}

document.querySelector('#change-button').onclick = () => request('coin.change');
document.querySelector('#reset-button').onclick = () => request('reset');

request();
