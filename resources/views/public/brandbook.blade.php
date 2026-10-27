<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <h1 class="mb-4">Brandbook</h1>
            <p class="lead mb-5">Overview of all available Bootstrap styles and components used in this application.</p>

            {{-- Typography --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Typography</h2>

                <div class="mb-4">
                    <h3>Headings</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <h1>Heading 1</h1>
                        <h2>Heading 2</h2>
                        <h3>Heading 3</h3>
                        <h4>Heading 4</h4>
                        <h5>Heading 5</h5>
                        <h6>Heading 6</h6>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Lead Text</h3>
                    <p class="lead bg-body-tertiary rounded p-4">
                        This is a lead paragraph. It stands out from regular paragraphs.
                    </p>
                </div>

                <div class="mb-4">
                    <h3>Inline Text</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <p>You can use the mark tag to <mark>highlight</mark> text.</p>
                        <p><del>This line of text is meant to be treated as deleted text.</del></p>
                        <p><s>This line of text is meant to be treated as no longer accurate.</s></p>
                        <p><ins>This line of text is meant to be treated as an addition to the document.</ins></p>
                        <p><u>This line of text will render as underlined.</u></p>
                        <p><small>This line of text is meant to be treated as fine print.</small></p>
                        <p><strong>This line rendered as bold text.</strong></p>
                        <p><em>This line rendered as italicized text.</em></p>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Blockquote</h3>
                    <blockquote class="blockquote bg-body-tertiary rounded p-4">
                        <p>A well-known quote to guide the reader.</p>
                        <footer class="blockquote-footer">
                            Someone famous in <cite title="Source Title">Source Title</cite>
                        </footer>
                    </blockquote>
                </div>

                <div class="mb-4">
                    <h3>List Unstyled</h3>
                    <ul class="list-unstyled bg-body-tertiary rounded p-4">
                        <li>This is</li>
                        <li>a list</li>
                        <li>of unstyled</li>
                        <li>items.</li>
                    </ul>
                </div>

                <div class="mb-4">
                    <h3>Inline List</h3>
                    <ul class="list-inline bg-body-tertiary rounded p-4">
                        <li class="list-inline-item">This is</li>
                        <li class="list-inline-item">an inline</li>
                        <li class="list-inline-item">list item.</li>
                    </ul>
                </div>
            </div>

            {{-- Tables --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Tables</h2>

                <div class="mb-4">
                    <h3>Table</h3>
                    <div class="table-responsive bg-body-tertiary rounded p-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Username</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Mark</td>
                                    <td>Otto</td>
                                    <td>@mdo</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Jacob</td>
                                    <td>Thornton</td>
                                    <td>@fat</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Larry</td>
                                    <td>the Bird</td>
                                    <td>@twitter</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Table Striped</h3>
                    <div class="table-responsive bg-body-tertiary rounded p-4">
                        <table class="table-striped table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Username</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Mark</td>
                                    <td>Otto</td>
                                    <td>@mdo</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Jacob</td>
                                    <td>Thornton</td>
                                    <td>@fat</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Larry</td>
                                    <td>the Bird</td>
                                    <td>@twitter</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Table Bordered</h3>
                    <div class="table-responsive bg-body-tertiary rounded p-4">
                        <table class="table-bordered table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Username</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Mark</td>
                                    <td>Otto</td>
                                    <td>@mdo</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Jacob</td>
                                    <td>Thornton</td>
                                    <td>@fat</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Table with Color Variants</h3>
                    <div class="table-responsive bg-body-tertiary rounded p-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Variant</th>
                                    <th>Example</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-primary">
                                    <td>Primary</td>
                                    <td>Shared rendering for both</td>
                                </tr>
                                <tr class="table-secondary">
                                    <td>Secondary</td>
                                    <td>Table rows and table cells</td>
                                </tr>
                                <tr class="table-success">
                                    <td>Success</td>
                                    <td>Supports all table elements</td>
                                </tr>
                                <tr class="table-danger">
                                    <td>Danger</td>
                                    <td>Like &lt;thead&gt;, &lt;tbody&gt;</td>
                                </tr>
                                <tr class="table-warning">
                                    <td>Warning</td>
                                    <td>&lt;tr&gt;, and &lt;td&gt;</td>
                                </tr>
                                <tr class="table-info">
                                    <td>Info</td>
                                    <td>Easy to style</td>
                                </tr>
                                <tr class="table-light">
                                    <td>Light</td>
                                    <td>Light background</td>
                                </tr>
                                <tr class="table-dark">
                                    <td>Dark</td>
                                    <td>Dark background</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Forms --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Forms</h2>

                <div class="mb-4">
                    <h3>Form Controls</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="mb-3">
                            <label for="exampleFormControlInput1" class="form-label">Email address</label>
                            <input
                                type="email"
                                class="form-control"
                                id="exampleFormControlInput1"
                                placeholder="name@example.com"
                            />
                        </div>
                        <div class="mb-3">
                            <label for="exampleFormControlTextarea1" class="form-label">Example textarea</label>
                            <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Select</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="mb-3">
                            <label for="exampleSelect" class="form-label">Example select</label>
                            <select class="form-select" id="exampleSelect">
                                <option selected>Open this select menu</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="exampleSelectSm" class="form-label">Small select</label>
                            <select class="form-select form-select-sm" id="exampleSelectSm">
                                <option selected>Open this select menu</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Checkboxes and Radios</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="flexCheckDefault" />
                            <label class="form-check-label" for="flexCheckDefault">Default checkbox</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="flexCheckChecked" checked />
                            <label class="form-check-label" for="flexCheckChecked">Checked checkbox</label>
                        </div>
                        <hr />
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="flexRadioDefault"
                                id="flexRadioDefault1"
                            />
                            <label class="form-check-label" for="flexRadioDefault1">Default radio</label>
                        </div>
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="flexRadioDefault"
                                id="flexRadioDefault2"
                                checked
                            />
                            <label class="form-check-label" for="flexRadioDefault2">Default checked radio</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Switch</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" />
                            <label class="form-check-label" for="flexSwitchCheckDefault"
                                >Default switch checkbox input</label>
                        </div>
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="flexSwitchCheckChecked"
                                checked
                            />
                            <label class="form-check-label" for="flexSwitchCheckChecked"
                                >Checked switch checkbox input</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Range</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <label for="customRange1" class="form-label">Example range</label>
                        <input type="range" class="form-range" id="customRange1" />
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Input Group</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="input-group mb-3">
                            <span class="input-group-text">@</span>
                            <input type="text" class="form-control" placeholder="Username" />
                        </div>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Recipient's username" />
                            <span class="input-group-text">@</span>
                            <input type="text" class="form-control" placeholder="Server" />
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">With textarea</span>
                            <textarea class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>File Input</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="mb-3">
                            <label for="formFile" class="form-label">Default file input example</label>
                            <input class="form-control" type="file" id="formFile" />
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Float Labels</h3>
                    <form class="bg-body-tertiary rounded p-4" autocomplete="off">
                        <div class="form-floating mb-3">
                            <input
                                type="email"
                                class="form-control"
                                id="floatingInput"
                                placeholder="name@example.com"
                            />
                            <label for="floatingInput">Email address</label>
                        </div>
                        <div class="form-floating">
                            <input type="password" class="form-control" id="floatingPassword" placeholder="Password" autocomplete="new-password" />
                            <label for="floatingPassword">Password</label>
                        </div>
                    </form>
                </div>

                <div class="mb-4">
                    <h3>Form Validation</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="mb-3">
                            <label for="validationServer01" class="form-label">Valid input</label>
                            <input
                                type="text"
                                class="form-control is-valid"
                                id="validationServer01"
                                value="Valid"
                                required
                            />
                            <div class="valid-feedback">Looks good!</div>
                        </div>
                        <div class="mb-3">
                            <label for="validationServer02" class="form-label">Invalid input</label>
                            <input
                                type="text"
                                class="form-control is-invalid"
                                id="validationServer02"
                                value="Invalid"
                                required
                            />
                            <div class="invalid-feedback">Please provide a valid input.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Buttons</h2>

                <div class="mb-4">
                    <h3>Button Variants</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <button type="button" class="btn btn-primary">Primary</button>
                        <button type="button" class="btn btn-secondary">Secondary</button>
                        <button type="button" class="btn btn-success">Success</button>
                        <button type="button" class="btn btn-danger">Danger</button>
                        <button type="button" class="btn btn-warning">Warning</button>
                        <button type="button" class="btn btn-info">Info</button>
                        <button type="button" class="btn btn-light">Light</button>
                        <button type="button" class="btn btn-dark">Dark</button>
                        <button type="button" class="btn btn-link">Link</button>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Outline Buttons</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <button type="button" class="btn btn-outline-primary">Primary</button>
                        <button type="button" class="btn btn-outline-secondary">Secondary</button>
                        <button type="button" class="btn btn-outline-success">Success</button>
                        <button type="button" class="btn btn-outline-danger">Danger</button>
                        <button type="button" class="btn btn-outline-warning">Warning</button>
                        <button type="button" class="btn btn-outline-info">Info</button>
                        <button type="button" class="btn btn-outline-light">Light</button>
                        <button type="button" class="btn btn-outline-dark">Dark</button>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Button Sizes</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <button type="button" class="btn btn-primary btn-lg">Large button</button>
                        <button type="button" class="btn btn-secondary btn-sm">Small button</button>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Disabled Buttons</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <button type="button" class="btn btn-primary" disabled>Primary (disabled)</button>
                        <button type="button" class="btn btn-secondary" disabled>Secondary (disabled)</button>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Block Level Buttons</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" type="button">Block level button</button>
                            <button class="btn btn-secondary" type="button">Block level button</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dropdowns --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Dropdowns</h2>

                <div class="mb-4">
                    <h3>Single Button Dropdown</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="dropdown d-inline-block">
                            <button
                                class="btn btn-secondary dropdown-toggle"
                                type="button"
                                data-toggle="dropdown"
                                aria-expanded="false"
                            >
                                Dropdown button
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                <li><a class="dropdown-item" href="#">Separated link</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Dropdown Variants</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="dropdown d-inline-block">
                            <button
                                class="btn btn-primary dropdown-toggle"
                                type="button"
                                data-toggle="dropdown"
                                aria-expanded="false"
                            >
                                Primary
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                            </ul>
                        </div>
                        <div class="dropdown d-inline-block">
                            <button
                                class="btn btn-success dropdown-toggle"
                                type="button"
                                data-toggle="dropdown"
                                aria-expanded="false"
                            >
                                Success
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                            </ul>
                        </div>
                        <div class="dropdown d-inline-block">
                            <button
                                class="btn btn-danger dropdown-toggle"
                                type="button"
                                data-toggle="dropdown"
                                aria-expanded="false"
                            >
                                Danger
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Nav --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Navigation</h2>

                <div class="mb-4">
                    <h3>Nav Tabs</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="#">Active</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Link</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Link</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link disabled" tabindex="-1" aria-disabled="true">Disabled</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Nav Pills</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="#">Active</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Link</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Link</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link disabled" tabindex="-1" aria-disabled="true">Disabled</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Navbar Example</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <nav class="navbar navbar-expand-lg bg-body-tertiary">
                            <div class="container-fluid">
                                <a class="navbar-brand" href="#">Navbar</a>
                                <button
                                    class="navbar-toggler"
                                    type="button"
                                    data-toggle="collapse"
                                    data-target="#navbarExample"
                                    aria-controls="navbarExample"
                                    aria-expanded="false"
                                    aria-label="Toggle navigation"
                                >
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="navbar-collapse collapse" id="navbarExample">
                                    <ul class="navbar-nav mb-lg-0 me-auto mb-2">
                                        <li class="nav-item">
                                            <a class="nav-link active" aria-current="page" href="#">Home</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Link</a>
                                        </li>
                                        <li class="nav-item dropdown">
                                            <a
                                                class="nav-link dropdown-toggle"
                                                href="#"
                                                role="button"
                                                data-toggle="dropdown"
                                                aria-expanded="false"
                                            >
                                                Dropdown
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Action</a></li>
                                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                                <li><hr class="dropdown-divider" /></li>
                                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                    <form class="d-flex" role="search">
                                        <input
                                            class="form-control me-2"
                                            type="search"
                                            placeholder="Search"
                                            aria-label="Search"
                                        />
                                        <button class="btn btn-outline-success" type="submit">Search</button>
                                    </form>
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>

            {{-- Cards --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Cards</h2>

                <div class="mb-4">
                    <h3>Basic Card</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="card" style="width: 18rem">
                            <div class="card-body">
                                <h5 class="card-title">Card title</h5>
                                <h6 class="card-subtitle text-body-secondary mb-2">Card subtitle</h6>
                                <p class="card-text">
                                    Some quick example text to build on the card title and make up the bulk of the
                                    card's content.
                                </p>
                                <a href="#" class="card-link">Card link</a>
                                <a href="#" class="card-link">Another link</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Card with Image</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="card" style="width: 18rem">
                            <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Placeholder" />
                            <div class="card-body">
                                <h5 class="card-title">Card title</h5>
                                <p class="card-text">
                                    Some quick example text to build on the card title and make up the bulk of the
                                    card's content.
                                </p>
                                <a href="#" class="btn btn-primary">Go somewhere</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Card Variants</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="row row-cols-1 row-cols-md-3 g-4">
                            <div class="col">
                                <div class="card card-outline-primary" style="width: 18rem">
                                    <div class="card-body">
                                        <h5 class="card-title">Primary card</h5>
                                        <p class="card-text">Some quick example text.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-outline-secondary" style="width: 18rem">
                                    <div class="card-body">
                                        <h5 class="card-title">Secondary card</h5>
                                        <p class="card-text">Some quick example text.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-outline-success" style="width: 18rem">
                                    <div class="card-body">
                                        <h5 class="card-title">Success card</h5>
                                        <p class="card-text">Some quick example text.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Pagination</h2>

                <div class="mb-4">
                    <h3>Default Pagination</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <nav aria-label="Page navigation example">
                            <ul class="pagination">
                                <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                                <li class="page-item"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Pagination with States</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <nav aria-label="Page navigation example">
                            <ul class="pagination">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#">Previous</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="#">1</a></li>
                                <li class="page-item active" aria-current="page">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Pagination Sizes</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <nav aria-label="Large pagination">
                            <ul class="pagination pagination-lg">
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                            </ul>
                        </nav>
                        <nav aria-label="Small pagination">
                            <ul class="pagination pagination-sm">
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            {{-- Badges --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Badges</h2>

                <div class="mb-4">
                    <h3>Badge Variants</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <span class="badge bg-primary">Primary</span>
                        <span class="badge bg-secondary">Secondary</span>
                        <span class="badge bg-success">Success</span>
                        <span class="badge bg-danger">Danger</span>
                        <span class="badge bg-warning text-body">Warning</span>
                        <span class="badge bg-info text-body">Info</span>
                        <span class="badge bg-light text-body">Light</span>
                        <span class="badge bg-dark">Dark</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Pill Badges</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <span class="badge rounded-pill bg-primary">Primary</span>
                        <span class="badge rounded-pill bg-secondary">Secondary</span>
                        <span class="badge rounded-pill bg-success">Success</span>
                        <span class="badge rounded-pill bg-danger">Danger</span>
                        <span class="badge rounded-pill bg-warning text-body">Warning</span>
                        <span class="badge rounded-pill bg-info text-body">Info</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Badges in Headings</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <h1>Example heading <span class="badge bg-secondary">New</span></h1>
                        <h2>Example heading <span class="badge bg-secondary">New</span></h2>
                        <h3>Example heading <span class="badge bg-secondary">New</span></h3>
                        <h4>Example heading <span class="badge bg-secondary">New</span></h4>
                        <h5>Example heading <span class="badge bg-secondary">New</span></h5>
                        <h6>Example heading <span class="badge bg-secondary">New</span></h6>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Badges in Buttons</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <button type="button" class="btn btn-primary">
                            Notifications <span class="badge bg-body">4</span>
                        </button>
                        <button type="button" class="btn btn-danger">
                            Alerts <span class="badge bg-body">12</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Alerts --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Alerts</h2>

                <div class="mb-4">
                    <h3>Alert Variants</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="alert alert-primary" role="alert">A simple primary alert—check it out!</div>
                        <div class="alert alert-secondary" role="alert">A simple secondary alert—check it out!</div>
                        <div class="alert alert-success" role="alert">A simple success alert—check it out!</div>
                        <div class="alert alert-danger" role="alert">A simple danger alert—check it out!</div>
                        <div class="alert alert-warning" role="alert">A simple warning alert—check it out!</div>
                        <div class="alert alert-info" role="alert">A simple info alert—check it out!</div>
                        <div class="alert alert-light" role="alert">A simple light alert—check it out!</div>
                        <div class="alert alert-dark" role="alert">A simple dark alert—check it out!</div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Alert with Link</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="alert alert-success" role="alert">
                            A simple success alert with <a href="#" class="alert-link">an example link</a>. Give it a
                            click if you like.
                        </div>
                        <div class="alert alert-danger" role="alert">
                            A simple danger alert with <a href="#" class="alert-link">an example link</a>. Give it a
                            click if you like.
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Alert with Additional Content</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <h4 class="alert-heading">Well done!</h4>
                            <p>
                                Aww yeah, you successfully read this important alert message. This example text is going
                                to run a bit longer so that you can see how spacing within an alert works with this kind
                                of content.
                            </p>
                            <hr />
                            <p class="mb-0">
                                Whenever you need to, be sure to use <strong>utility classes</strong> to adjust the
                                spacing and alignment.
                            </p>
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Close Button --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Close Button</h2>

                <div class="mb-4">
                    <h3>Examples</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <p>
                            Close button example:
                            <button type="button" class="btn-close" aria-label="Close"></button>
                        </p>
                        <p>
                            Disabled close button:
                            <button type="button" class="btn-close" aria-label="Close" disabled></button>
                        </p>
                        <p>
                            Flipped (for dark backgrounds):
                            <button type="button" class="btn-close btn-close-white" aria-label="Close"></button>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Helpers --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Helpers</h2>

                <div class="mb-4">
                    <h3>Ratio (Responsive Embeds)</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="ratio ratio-16x9">
                            <iframe
                                src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                                title="YouTube video"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Position</h3>
                    <div class="bg-body-tertiary position-relative rounded p-4" style="height: 200px">
                        <div class="position-absolute start-0 top-0">Top left</div>
                        <div class="position-absolute end-0 top-0">Top right</div>
                        <div class="position-absolute start-0 bottom-0">Bottom left</div>
                        <div class="position-absolute end-0 bottom-0">Bottom right</div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Visually Hidden</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <a href="#main" class="visually-hidden visually-hidden-focusable">Skip to main content</a>
                        <p>The "Skip to main content" link above is visually hidden but becomes visible on focus.</p>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Stretched Link</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="btn btn-primary">
                            <a href="#" class="stretched-link">Stretched link</a>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Color Background Utilities</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="bg-primary text-body mb-2 p-3">.bg-primary</div>
                        <div class="bg-secondary text-body mb-2 p-3">.bg-secondary</div>
                        <div class="bg-success text-body mb-2 p-3">.bg-success</div>
                        <div class="bg-danger text-body mb-2 p-3">.bg-danger</div>
                        <div class="bg-warning text-body mb-2 p-3">.bg-warning</div>
                        <div class="bg-info text-body mb-2 p-3">.bg-info</div>
                        <div class="bg-light text-body mb-2 p-3">.bg-light</div>
                        <div class="bg-dark text-body mb-2 p-3">.bg-dark</div>
                        <div class="bg-body-tertiary mb-2 p-3">.bg-body-tertiary</div>
                        <div class="bg-body-tertiary mb-2 p-3">.bg-body-tertiary</div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Text Color Utilities</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <p class="text-primary">.text-primary</p>
                        <p class="text-secondary">.text-secondary</p>
                        <p class="text-success">.text-success</p>
                        <p class="text-danger">.text-danger</p>
                        <p class="text-warning">.text-warning</p>
                        <p class="text-info">.text-info</p>
                        <p class="text-light bg-dark d-inline-block p-1">.text-light</p>
                        <p class="text-dark">.text-dark</p>
                        <p class="text-body">.text-body</p>
                        <p class="text-body-secondary">.text-body-secondary</p>
                        <p class="text-body-tertiary">.text-body-tertiary</p>
                        <p class="text-muted">.text-muted</p>
                        <p class="text-black-50">.text-black-50</p>
                        <p class="text-white-50">.text-white-50</p>
                        <p class="bg-dark d-inline-block p-1 text-white">.text-white</p>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Border Utilities</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="mb-2 border p-3">.border</div>
                        <div class="border-top mb-2 p-3">.border-top</div>
                        <div class="border-end mb-2 p-3">.border-end</div>
                        <div class="border-bottom mb-2 p-3">.border-bottom</div>
                        <div class="border-start mb-2 p-3">.border-start</div>
                        <div class="border-primary mb-2 p-3">.border-primary</div>
                        <div class="border-success mb-2 p-3">.border-success</div>
                        <div class="border-danger mb-2 p-3">.border-danger</div>
                        <div class="mb-2 border-0 p-3">.border-0 (no border)</div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Border Radius</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <span class="d-inline-block bg-secondary mb-2 rounded p-3">.rounded</span>
                        <span class="d-inline-block bg-secondary rounded-top mb-2 p-3">.rounded-top</span>
                        <span class="d-inline-block bg-secondary rounded-start mb-2 p-3">.rounded-start</span>
                        <span class="d-inline-block bg-secondary rounded-bottom mb-2 p-3">.rounded-bottom</span>
                        <span class="d-inline-block bg-secondary rounded-end mb-2 p-3">.rounded-end</span>
                        <span class="d-inline-block bg-secondary rounded-circle mb-2 p-3">.rounded-circle</span>
                        <span class="d-inline-block bg-secondary rounded-pill mb-2 p-3">.rounded-pill</span>
                        <span class="d-inline-block bg-secondary rounded-1 mb-2 p-3">.rounded-1</span>
                        <span class="d-inline-block bg-secondary rounded-2 mb-2 p-3">.rounded-2</span>
                        <span class="d-inline-block bg-secondary rounded-3 mb-2 p-3">.rounded-3</span>
                        <span class="d-inline-block bg-secondary rounded-5 mb-2 p-3">.rounded-5</span>
                        <span class="d-inline-block bg-secondary rounded-circle mb-2 p-3">.rounded-circle</span>
                        <span class="d-inline-block bg-secondary rounded-pill mb-2 p-3">.rounded-pill</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Clearfix</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="clearfix">
                            <span class="float-start">Float start</span>
                            <span class="float-end">Float end</span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Links</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <a href="#" class="link-primary">Primary link</a><br />
                        <a href="#" class="link-success">Success link</a><br />
                        <a href="#" class="link-danger">Danger link</a><br />
                        <a href="#" class="link-warning">Warning link</a><br />
                        <a href="#" class="link-info">Info link</a><br />
                        <a href="#" class="link-dark">Dark link</a>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Stacks (D-flex, D-grid, Gap)</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="d-flex gap-3">
                            <div class="bg-primary p-2">Flex item 1</div>
                            <div class="bg-secondary p-2">Flex item 2</div>
                            <div class="bg-success p-2">Flex item 3</div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Wrap Text / Truncate</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="row">
                            <div class="col-2">
                                <div class="bg-warning text-body p-2 text-nowrap" style="width: 80px">
                                    This text will not wrap, but will truncate instead.
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="bg-warning text-body text-truncate p-2" style="width: 80px">
                                    This text will truncate with an ellipsis.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Images --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Images</h2>

                <div class="mb-4">
                    <h3>Responsive Image</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <img src="https://via.placeholder.com/300x200" class="img-fluid" alt="Responsive image" />
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Thumbnail</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <img src="https://via.placeholder.com/300x200" class="img-thumbnail" alt="Image thumbnail" />
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Image Alignment</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <img
                            src="https://via.placeholder.com/100x100"
                            class="float-start me-3 rounded"
                            alt="Float start"
                        />
                        <img src="https://via.placeholder.com/100x100" class="float-end ms-3 rounded" alt="Float end" />
                        <p>This text wraps around the floated images on either side.</p>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>

            {{-- Grid --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Grid</h2>

                <div class="mb-4">
                    <h3>Equal Width</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="row">
                            <div class="col bg-primary text-body p-2">1 of 2</div>
                            <div class="col bg-secondary text-body p-2">2 of 2</div>
                        </div>
                        <div class="row">
                            <div class="col bg-primary text-body p-2">1 of 3</div>
                            <div class="col bg-secondary text-body p-2">2 of 3</div>
                            <div class="col bg-success text-body p-2">3 of 3</div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Grid Columns</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="row">
                            <div class="bg-primary text-body col-4 p-2">col-4</div>
                            <div class="bg-secondary text-body col-8 p-2">col-8</div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3>Responsive Grid</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="row">
                            <div class="col-md-8 bg-primary text-body p-2">col-md-8</div>
                            <div class="col-md-4 bg-secondary text-body p-2">col-md-4</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Containers --}}
            <div class="mb-5">
                <h2 class="border-bottom mb-4 pb-2">Containers</h2>

                <div class="mb-4">
                    <h3>Container Types</h3>
                    <div class="bg-body-tertiary rounded p-4">
                        <div class="bg-primary text-body container mb-2 p-2">.container</div>
                        <div class="container-sm bg-secondary text-body mb-2 p-2">.container-sm</div>
                        <div class="container-md bg-success text-body mb-2 p-2">.container-md</div>
                        <div class="container-lg bg-danger text-body mb-2 p-2">.container-lg</div>
                        <div class="container-xl bg-warning text-body mb-2 p-2">.container-xl</div>
                        <div class="container-xxl bg-info text-body mb-2 p-2">.container-xxl</div>
                        <div class="container-fluid bg-dark text-body mb-2 p-2">.container-fluid</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
