/* This class is part of the XP framework's EAS connectivity
 *
 * $Id$
 */

package net.xp_framework.easc.jmx.jboss;

import org.jboss.system.ServiceMBeanSupport;
import net.xp_framework.easc.server.ServerThread;
import net.xp_framework.easc.server.ServerContext;
import net.xp_framework.easc.server.InvocationServerContext;
import net.xp_framework.easc.protocol.standard.InvocationServerHandler;
import net.xp_framework.easc.jmx.jboss.EascServiceMBean;
import java.net.ServerSocket;
import net.xp_framework.easc.protocol.standard.Serializer;
import net.xp_framework.easc.protocol.standard.SerializerContext;
import net.xp_framework.easc.protocol.standard.Invokeable;
import javax.ejb.EJBObject;

/**
 * EASC service managed bean
 *
 * @see   org.jboss.system.ServiceMBeanSupport
 */
public class EascService extends ServiceMBeanSupport implements EascServiceMBean {
    private int port= 0;
    private ServerThread serverThread= null;
    
    /**
     * Sets the port the server thread will listen on
     *
     * @access  public
     * @param   int port
     */
    public void setPort(int port) {
        this.port= port;
    }

    /**
     * Gets the port the server thread is listening on
     *
     * @access  public
     * @return  int
     */
    public int getPort() {
        return this.port;
    }
    
    /**
     * Enable EJB3 support if available
     *
     * @access  protected
     * @param   net.xp_framework.easc.server.ServerContext ctx
     */
    protected void setupEJB3Support(final ServerContext ctx) {
        try {
            Class jbossProxyClass= Class.forName("org.jboss.ejb3.JBossProxy");
            System.out.println("EASC: Enabling JBoss-EJB3 support");
            
            Serializer.registerMapping(jbossProxyClass, new Invokeable<String, org.jboss.ejb3.JBossProxy>() {
                public String invoke(org.jboss.ejb3.JBossProxy p, Object arg) throws Exception {
                    ctx.objects.put(p.hashCode(), p);

                    // Find out the correct interface
                    Class ejbInterface= null;
                    for (Class iface: p.getClass().getInterfaces()) {
                        if (EJBObject.class.isAssignableFrom(iface) || org.jboss.ejb3.JBossProxy.class.isAssignableFrom(iface)) continue;

                        // First interface implemented is the "real" interface
                        ejbInterface= iface;
                        break;
                    }

                    return "I:" + p.hashCode() + ":{" + Serializer.representationOf(
                        ejbInterface.getName(),
                        (SerializerContext)arg
                    ) + "}";
                }
            });
        } catch (ClassNotFoundException ignored) {
          // No EJB3 deployment
          System.out.println("EASC: No JBoss-EJB3 support enabled.");
        }
    }
    
    /**
     * Starts EASC service
     *
     * @access  protected
     * @throws  java.lang.Exception
     * @see     org.jboss.system.ServiceMBeanSupport#startService
     */
    protected void startService() throws Exception {
        ServerContext ctx= new InvocationServerContext();
        this.serverThread= new ServerThread(new ServerSocket(this.port));
        this.serverThread.setHandler(new InvocationServerHandler());
        this.serverThread.setContext(ctx);
        this.setupEJB3Support(ctx);
        this.serverThread.start();
    }
    
    /**
     * Stops EASC service
     *
     * @access  protected
     * @throws  java.lang.Exception
     * @see     org.jboss.system.ServiceMBeanSupport#stopService
     */
    protected void stopService() throws Exception {
        this.serverThread.shutdown();
    }
}
