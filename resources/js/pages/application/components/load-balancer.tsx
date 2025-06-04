import { Head, useForm, usePage } from '@inertiajs/react';
import { Site } from '@/types/site';
import ServerLayout from '@/layouts/server/layout';
import { Server } from '@/types/server';
import Container from '@/components/container';
import HeaderContainer from '@/components/header-container';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { BookOpenIcon, LoaderCircleIcon } from 'lucide-react';
import { FormEvent } from 'react';
import { LoadBalancerServer } from '@/types/load-balancer-server';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Form, FormField, FormFields } from '@/components/ui/form';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/ui/input-error';
import FormSuccessful from '@/components/form-successful';

export default function LoadBalancer() {
  const page = usePage<{
    server: Server;
    site: Site;
    loadBalancerServers: LoadBalancerServer[];
  }>();

  const form = useForm<{
    method: 'round-robin' | 'least-connections' | 'ip-hash';
    servers: {
      server: string;
      port: string;
      weight: string;
      backup: boolean;
    }[];
  }>({
    method: 'round-robin',
    servers: [],
  });

  const submit = (e: FormEvent) => {
    e.preventDefault();
    form.post(route('application.update-load-balancer', { server: page.props.server.id, site: page.props.site.id }), {
      onSuccess: () => {
        form.reset();
      },
      preserveScroll: true,
    });
  };

  return (
    <ServerLayout>
      <Head title={`${page.props.site.domain} - ${page.props.server.name}`} />

      <Container className="max-w-5xl">
        <HeaderContainer>
          <Heading title="Load balancer" description="Here you can manage the load balancer configs" />
          <div className="flex items-center gap-2">
            <a href="https://vitodeploy.com/docs/sites/load-balancer" target="_blank">
              <Button variant="outline">
                <BookOpenIcon />
                <span className="hidden lg:block">Docs</span>
              </Button>
            </a>
          </div>
        </HeaderContainer>

        <Card>
          <CardHeader>
            <CardTitle>Configs</CardTitle>
            <CardDescription>Modify load balancer configs</CardDescription>
          </CardHeader>
          <CardContent className="p-4">
            <Form>
              <FormFields>
                <FormField>
                  <Label htmlFor="method">Method</Label>
                  <Select
                    value={form.data.method}
                    onValueChange={(value) => form.setData('method', value as 'round-robin' | 'least-connections' | 'ip-hash')}
                  >
                    <SelectTrigger id="method">
                      <SelectValue placeholder="Select a method" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectGroup>
                        <SelectItem value="round-robin">round-robin</SelectItem>
                        <SelectItem value="least-connections">least-connections</SelectItem>
                        <SelectItem value="ip-hash">ip-hash</SelectItem>
                      </SelectGroup>
                    </SelectContent>
                  </Select>
                  <InputError message={form.errors.method} />
                </FormField>
              </FormFields>
            </Form>
          </CardContent>
          <CardFooter>
            <Button disabled={form.processing} onClick={submit}>
              {form.processing && <LoaderCircleIcon className="animate-spin" />}
              <FormSuccessful successful={form.recentlySuccessful} />
              Save and Deploy
            </Button>
          </CardFooter>
        </Card>
      </Container>
    </ServerLayout>
  );
}
