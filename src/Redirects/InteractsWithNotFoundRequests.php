<?php

namespace FewFar\Sitekit\Redirects;

use Illuminate\Http\Request;

trait InteractsWithNotFoundRequests
{
/**
     * Current request instance.
     */
    protected ?Request $request = null;

    /**
     * Set current Request instance.
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Current request instance.
     */
    public function request(): ?Request
    {
        return $this->request;
    }

    /**
     * Current path of the request with leading slash.
     */
    protected ?string $path = null;

    /**
     * Set the path of the request.
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Current path of the request with leading slash.
     */
    public function path(): ?string
    {
        return $this->path;
    }

    /**
     * Redirect model, if found.
     */
    protected ?Redirect $redirect = null;

    /**
     * Set the Redirect model.
     */
    public function setRedirect(?Redirect $redirect): static
    {
        $this->redirect = $redirect;

        return $this;
    }

    /**
     * Redirect model, if found.
     */
    public function redirect(): ?Redirect
    {
        return $this->redirect;
    }

    /**
     * All requests are logged by default, set this to add bespoke behavior.
     *
     * @var callable
     */
    protected $shouldLogCallback = null;

    /**
     * Sets should log callback.
     */
    public function setShouldLogCallback(?callable $callable): static
    {
        $this->shouldLogCallback = $callable;

        return $this;
    }

    /**
     * Current should log callback.
     */
    public function shouldLogCallback(): ?callable
    {
        return $this->shouldLogCallback;
    }
}
